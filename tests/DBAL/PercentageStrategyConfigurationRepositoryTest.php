<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class PercentageStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsConfiguredForUnderlyingConnectionOnly()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new PercentageStrategyConfigurationRepository($DBALConnection);
        $repository->configureSchema($schema = new Schema(), $DBALConnection);

        static::assertCount(1, $schema->getTables());

        $otherDBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository->configureSchema($schema = new Schema(), $otherDBALConnection);

        static::assertEmpty($schema->getTables());
    }


    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new PercentageStrategyConfigurationRepository($DBALConnection);
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
