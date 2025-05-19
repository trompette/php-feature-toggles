<?php

namespace Trompette\FeatureToggles\DBAL;

use Assert\Assert;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Trompette\FeatureToggles\WhitelistStrategy\ConfigurationRepository;

final class WhitelistStrategyConfigurationRepository extends SchemaMigrator implements ConfigurationRepository
{
    public function getWhitelistedTargets(string $feature): array
    {
        $sql = 'select target from feature_toggles_whitelist where feature = ?';

        $targets = $this->connection->executeQuery($sql, [$feature])->fetchFirstColumn();

        Assert::thatAll($targets)->string();

        return $targets;
    }

    public function listFeatures(): array
    {
        $sql = 'select distinct feature from feature_toggles_whitelist';

        $features = $this->connection->fetchFirstColumn($sql);

        Assert::thatAll($features)->string();

        return $features;
    }

    public function removefeature(string $feature): void
    {
        $this->connection->delete(
            'feature_toggles_whitelist',
            [
                'feature' => $feature,
            ]
        );
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
            $schema->dropTable('feature_toggles_whitelist');
        }

        $table = $schema->createTable('feature_toggles_whitelist');
        $table->addColumn('feature', Types::STRING);
        $table->addColumn('target', Types::STRING);
        $table->addIndex(['feature'], 'feature_toggles_whitelist_feature_idx');
    }
}
