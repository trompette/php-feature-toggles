<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
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

    abstract protected function createRepository();

    public function testSchemaIsConfiguredForUnderlyingConnectionOnly()
    {
        $this->repository->configureSchema($schema = new Schema(), $this->connection);
        static::assertCount(1, $schema->getTables());

        $anotherConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->repository->configureSchema($schema = new Schema(), $anotherConnection);
        static::assertEmpty($schema->getTables());
    }

    public function testSchemaIsMigrated()
    {
        $this->repository->migrateSchema();
        static::assertCount(1, $this->connection->getSchemaManager()->listTables());
    }
}
