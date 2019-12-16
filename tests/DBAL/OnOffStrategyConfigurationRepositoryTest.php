<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class OnOffStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new OnOffStrategyConfigurationRepository($DBALConnection);

        $this->assertCount(0, $DBALConnection->getSchemaManager()->listTables());
        $repository->migrateSchema();
        $this->assertCount(1, $DBALConnection->getSchemaManager()->listTables());
    }

    public function testConfigurationIsPersisted()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new OnOffStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        $this->assertFalse($repository->isEnabled('feature'));
        $repository->setEnabled(true, 'feature');
        $this->assertTrue($repository->isEnabled('feature'));
        $repository->setEnabled(false, 'feature');
        $this->assertFalse($repository->isEnabled('feature'));
    }
}
