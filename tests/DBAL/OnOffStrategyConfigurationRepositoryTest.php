<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use PHPUnit\Framework\TestCase;

class OnOffStrategyConfigurationRepositoryTest extends TestCase
{
    public function testSchemaIsConfiguredForUnderlyingConnectionOnly()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new OnOffStrategyConfigurationRepository($DBALConnection);
        $repository->configureSchema($schema = new Schema(), $DBALConnection);

        static::assertCount(1, $schema->getTables());

        $otherDBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository->configureSchema($schema = new Schema(), $otherDBALConnection);

        static::assertEmpty($schema->getTables());
    }

    public function testSchemaIsMigrated()
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $repository = new OnOffStrategyConfigurationRepository($DBALConnection);
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
