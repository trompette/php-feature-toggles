<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Trompette\FeatureToggles\WhitelistStrategy\ConfigurationRepository;

class WhitelistStrategyConfigurationRepository extends SchemaMigrator implements ConfigurationRepository
{
    public function getWhitelistedTargets(string $feature): array
    {
        $sql = 'select target from feature_toggles_whitelist where feature = ?';

        return $this->connection->executeQuery($sql, [$feature])->fetchFirstColumn();
    }

    public function addToWhitelist(string $target, string $feature): void
    {
        $this->connection->insert('feature_toggles_whitelist', [
            'feature' => $feature,
            'target' => $target,
        ]);
    }

    public function removeFromWhitelist(string $target, string $feature): void
    {
        $sql = 'delete from feature_toggles_whitelist where feature = ? and target = ?';

        $this->connection->executeQuery($sql, [$feature, $target]);
    }

    public function configureSchema(Schema $schema, Connection $connection): void
    {
        if ($connection !== $this->connection) {
            return;
        }

        if ($schema->hasTable('feature_toggles_whitelist')) {
            return;
        }

        $table = $schema->createTable('feature_toggles_whitelist');
        $table->addColumn('feature', Types::STRING);
        $table->addColumn('target', Types::STRING);
        $table->addIndex(['feature'], 'feature_toggles_whitelist_feature_idx');
    }
}
