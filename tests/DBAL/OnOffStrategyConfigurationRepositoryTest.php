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

        static::assertCount(0, $DBALConnection->getSchemaManager()->listTables());
        $repository->migrateSchema();
        static::assertCount(1, $DBALConnection->getSchemaManager()->listTables());
    }

    public function testConfigurationIsPersisted()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new OnOffStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        static::assertFalse($repository->isEnabled('feature'));
        $repository->setEnabled(true, 'feature');
        static::assertTrue($repository->isEnabled('feature'));
        $repository->setEnabled(false, 'feature');
        static::assertFalse($repository->isEnabled('feature'));
    }
}
