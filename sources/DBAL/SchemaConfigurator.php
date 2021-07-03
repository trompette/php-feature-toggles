<?php

namespace Trompette\FeatureToggles\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

interface SchemaConfigurator
{
    public function configureSchema(Schema $schema, Connection $connection): void;
}
