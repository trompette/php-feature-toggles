<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Trompette\FeatureToggles\PercentageStrategy\ConfigurationRepository;

class PercentageStrategyConfigurationRepository extends SchemaMigrator implements ConfigurationRepository
{
    public function getPercentage(string $feature): int
    {
        $sql = 'select percentage from feature_toggles_percentage where feature = ?';
        $column = $this->connection->fetchOne($sql, [$feature]);

        return false !== $column ? (int) filter_var($column, FILTER_SANITIZE_NUMBER_INT) : 0;
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

    public function configureSchema(Schema $schema, Connection $connection): void
    {
        if ($connection !== $this->connection) {
            return;
        }

        if ($schema->hasTable('feature_toggles_percentage')) {
            $schema->dropTable('feature_toggles_percentage');
        }

        $table = $schema->createTable('feature_toggles_percentage');
        $table->addColumn('feature', Types::STRING);
        $table->addColumn('percentage', Types::SMALLINT);
        $table->setPrimaryKey(['feature'], 'feature_toggles_percentage_pk');
    }
}
