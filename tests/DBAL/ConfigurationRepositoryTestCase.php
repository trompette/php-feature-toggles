<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\DBAL\SchemaMigrator;

abstract class ConfigurationRepositoryTestCase extends TestCase
{
    protected Connection $connection;
    protected SchemaMigrator $repository;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->createRepository();
    }

    abstract protected function createRepository(): void;

    public function testSchemaIsConfiguredForUnderlyingConnectionOnly(): void
    {
        $this->repository->configureSchema($schema = new Schema(), $this->connection);
        static::assertCount(1, $schema->getTables());

        $anotherConnection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->repository->configureSchema($schema = new Schema(), $anotherConnection);
        static::assertEmpty($schema->getTables());
    }

    public function testAlteredSchemaCanBeMigrated(): void
    {
        $schemaManager = $this->connection->createSchemaManager();
        $comparator = $schemaManager->createComparator();

        $this->repository->migrateSchema();
        $expectedSchema = $schemaManager->introspectSchema();
        $expectedTables = $expectedSchema->getTables();
        static::assertNotEmpty($expectedTables);

        $fromTable = reset($expectedTables);
        $toTable = clone $fromTable;
        $toTable->addColumn(uniqid('c_'), Types::INTEGER, ['default' => 0]);
        $tableDiff = $comparator->compareTables($fromTable, $toTable);
        $schemaManager->alterTable($tableDiff);
        static::assertNotEquals($expectedSchema, $schemaManager->introspectSchema());

        $this->repository->migrateSchema();
        static::assertEquals($expectedSchema, $schemaManager->introspectSchema());
    }
}
