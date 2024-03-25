<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\DBAL\SchemaMigrator;

abstract class ConfigurationRepositoryTest extends TestCase
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

        $this->repository->migrateSchema();
        $schema = $schemaManager->introspectSchema();
        static::assertCount(1, $tables = $schema->getTables());

        $table = $schema->getTable((string) array_key_first($tables));
        $schemaManager->alterTable($this->createTableDiff($table));
        static::assertNotEquals($schema, $schemaManager->introspectSchema());

        $this->repository->migrateSchema();
        static::assertEquals($schema, $schemaManager->introspectSchema());
    }

    private function createTableDiff(Table $table): TableDiff
    {
        $tableDiff = new TableDiff($table->getName());
        $tableDiff->addedColumns[] = new Column(uniqid('c_'), Type::getType(Types::INTEGER), ['default' => 0]);
        $tableDiff->fromTable = $table;

        return $tableDiff;
    }
}
