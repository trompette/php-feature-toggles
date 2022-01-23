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
    /** @var Connection */
    protected $connection;

    /** @var SchemaMigrator */
    protected $repository;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->createRepository();
    }

    abstract protected function createRepository(): void;

    public function testSchemaIsConfiguredForUnderlyingConnectionOnly(): void
    {
        $this->repository->configureSchema($schema = new Schema(), $this->connection);
        static::assertCount(1, $schema->getTables());

        $anotherConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->repository->configureSchema($schema = new Schema(), $anotherConnection);
        static::assertEmpty($schema->getTables());
    }

    public function testAlteredSchemaCanBeMigrated(): void
    {
        $schemaManager = $this->connection->getSchemaManager();

        $this->repository->migrateSchema();
        $schema = $schemaManager->createSchema();
        static::assertCount(1, $tables = $schema->getTables());

        $schemaManager->alterTable($this->createTableDiff(current($tables)));
        static::assertNotEquals($schema, $schemaManager->createSchema());

        $this->repository->migrateSchema();
        static::assertEquals($schema, $schemaManager->createSchema());
    }

    private function createTableDiff(Table $table): TableDiff
    {
        $tableDiff = new TableDiff($table->getName());
        $tableDiff->addedColumns[] = new Column(uniqid('c_'), Type::getType(Types::INTEGER), ['default' => 0]);
        $tableDiff->fromTable = $table;

        return $tableDiff;
    }
}
