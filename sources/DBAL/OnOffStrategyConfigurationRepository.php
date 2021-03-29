<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\StringType;
use Trompette\FeatureToggles\OnOffStrategy\ConfigurationRepository;

class OnOffStrategyConfigurationRepository implements ConfigurationRepository, SchemaMigrator
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function isEnabled(string $feature): bool
    {
        $sql = 'select enabled from feature_toggles_onoff where feature = ?';

        return (bool) $this->connection->fetchColumn($sql, [$feature]);
    }

    public function setEnabled(bool $enabled, string $feature): void
    {
        $this->connection->transactional(
            function (Connection $connection) use ($enabled, $feature) {
                $rowCount = $connection->update(
                    'feature_toggles_onoff',
                    [
                        'enabled' => $enabled,
                    ],
                    [
                        'feature' => $feature,
                    ],
                    [
                        'enabled' => ParameterType::BOOLEAN,
                    ]
                );

                if (0 === $rowCount) {
                    $connection->insert(
                        'feature_toggles_onoff',
                        [
                            'feature' => $feature,
                            'enabled' => $enabled,
                        ],
                        [
                            'enabled' => ParameterType::BOOLEAN,
                        ]
                    );
                }
            }
        );
    }

    public function migrateSchema(): void
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(['feature_toggles_onoff'])) {
            return;
        }

        $schemaManager->createTable(new Table(
            'feature_toggles_onoff',
            [
                new Column('feature', new StringType()),
                new Column('enabled', new BooleanType()),
            ],
            [
                new Index('feature_toggles_onoff_pk', ['feature'], true, true),
            ]
        ));
    }
}
