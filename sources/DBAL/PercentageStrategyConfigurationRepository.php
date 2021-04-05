<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Trompette\FeatureToggles\PercentageStrategy\ConfigurationRepository;

class PercentageStrategyConfigurationRepository implements ConfigurationRepository, SchemaMigrator
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getPercentage(string $feature): int
    {
        $sql = 'select percentage from feature_toggles_percentage where feature = ?';
        $column = $this->connection->fetchOne($sql, [$feature]);

        return false !== $column ? $column : 0;
    }

    public function setPercentage(int $percentage, string $feature): void
    {
        $this->connection->transactional(
            function (Connection $connection) use ($percentage, $feature) {
                $rowCount = $connection->update(
                    'feature_toggles_percentage',
                    ['percentage' => $percentage],
                    ['feature' => $feature]
                );

                if (0 === $rowCount) {
                    $connection->insert('feature_toggles_percentage', [
                        'feature' => $feature,
                        'percentage' => $percentage,
                    ]);
                }
            }
        );
    }

    public function migrateSchema(): void
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(['feature_toggles_percentage'])) {
            return;
        }

        $schemaManager->createTable(new Table(
            'feature_toggles_percentage',
            [
                new Column('feature', new StringType()),
                new Column('percentage', new SmallIntType()),
            ],
            [
                new Index('feature_toggles_percentage_pk', ['feature'], true, true),
            ]
        ));
    }
}
