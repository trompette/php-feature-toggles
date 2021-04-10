<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class PercentageStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new PercentageStrategyConfigurationRepository($DBALConnection);

        static::assertCount(0, $DBALConnection->getSchemaManager()->listTables());
        $repository->migrateSchema();
        static::assertCount(1, $DBALConnection->getSchemaManager()->listTables());
    }

    public function testConfigurationIsPersisted()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new PercentageStrategyConfigurationRepository($DBALConnection);
        $repository->migrateSchema();

        static::assertSame(0, $repository->getPercentage('feature'));
        $repository->setPercentage(25, 'feature');
        static::assertSame(25, $repository->getPercentage('feature'));
    }
}
