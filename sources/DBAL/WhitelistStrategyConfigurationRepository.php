<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\StringType;
use Trompette\FeatureToggles\WhitelistStrategy\ConfigurationRepository;

class WhitelistStrategyConfigurationRepository implements ConfigurationRepository, SchemaMigrator
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

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

    public function migrateSchema(): void
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(['feature_toggles_whitelist'])) {
            return;
        }

        $schemaManager->createTable(new Table(
            'feature_toggles_whitelist',
            [
                new Column('feature', new StringType()),
                new Column('target', new StringType()),
            ],
            [
                new Index('feature_toggles_whitelist_feature_idx', ['feature']),
            ]
        ));
    }
}
