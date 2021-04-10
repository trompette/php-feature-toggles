<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class WhitelistStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new WhitelistStrategyConfigurationRepository($DBALConnection);

        static::assertCount(0, $DBALConnection->getSchemaManager()->listTables());
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
