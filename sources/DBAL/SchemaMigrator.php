<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;

abstract class SchemaMigrator implements SchemaConfigurator
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function migrateSchema(): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        $schema = $schemaManager->introspectSchema();
        $this->configureSchema($schema, $this->connection);

        $schemaManager->migrateSchema($schema);
    }
}
