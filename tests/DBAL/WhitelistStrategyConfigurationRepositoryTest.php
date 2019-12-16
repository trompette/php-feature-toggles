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

        $this->assertCount(0, $DBALConnection->getSchemaManager()->listTables());
        $repository->migrateSchema();
        $this->assertCount(1, $DBALConnection->getSchemaManager()->listTables());
    }

    public function testConfigurationIsPersisted()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        $this->assertEmpty($repository->getWhitelistedTargets('feature'));
        $repository->addToWhitelist('target', 'feature');
        $this->assertSame(['target'], $repository->getWhitelistedTargets('feature'));
        $repository->removeFromWhitelist('target', 'feature');
        $this->assertEmpty($repository->getWhitelistedTargets('feature'));
    }
}
