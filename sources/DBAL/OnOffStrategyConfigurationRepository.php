<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Trompette\FeatureToggles\OnOffStrategy\ConfigurationRepository;

class OnOffStrategyConfigurationRepository extends SchemaMigrator implements ConfigurationRepository
{
    public function isEnabled(string $feature): bool
    {
        $sql = 'select enabled from feature_toggles_onoff where feature = ?';

        return (bool) $this->connection->fetchOne($sql, [$feature]);
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

    public function configureSchema(Schema $schema, Connection $connection): void
    {
        if ($connection !== $this->connection) {
            return;
        }

        if ($schema->hasTable('feature_toggles_onoff')) {
            $schema->dropTable('feature_toggles_onoff');
        }

        $table = $schema->createTable('feature_toggles_onoff');
        $table->addColumn('feature', Types::STRING);
        $table->addColumn('enabled', Types::BOOLEAN);
        $table->setPrimaryKey(['feature'], 'feature_toggles_onoff_pk');
    }
}
