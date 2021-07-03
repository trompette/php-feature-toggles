<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;

abstract class SchemaMigrator implements SchemaConfigurator
{
    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function migrateSchema(): void
    {
        $platform = $this->connection->getDatabasePlatform();
        $fromSchema = $this->connection->getSchemaManager()->createSchema();

        $this->configureSchema($toSchema = clone $fromSchema, $this->connection);

        foreach ($toSchema->getMigrateFromSql($fromSchema, $platform) as $statement) {
            $this->connection->executeStatement($statement);
        }
    }
}
