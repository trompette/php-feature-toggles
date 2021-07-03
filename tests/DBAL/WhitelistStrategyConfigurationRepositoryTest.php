<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class WhitelistStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsConfiguredForUnderlyingConnectionOnly()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $repository->configureSchema($schema = new Schema(), $DBALConnection);

        static::assertCount(1, $schema->getTables());

        $otherDBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository->configureSchema($schema = new Schema(), $otherDBALConnection);

        static::assertEmpty($schema->getTables());
    }


    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        static::assertCount(1, $DBALConnection->getSchemaManager()->listTables());
    }

    public function testConfigurationIsPersisted()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        static::assertEmpty($repository->getWhitelistedTargets('feature'));
        $repository->addToWhitelist('target', 'feature');
        static::assertSame(['target'], $repository->getWhitelistedTargets('feature'));
        $repository->removeFromWhitelist('target', 'feature');
        static::assertEmpty($repository->getWhitelistedTargets('feature'));
    }
}
