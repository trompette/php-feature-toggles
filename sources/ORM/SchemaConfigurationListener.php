<?php

namespace Trompette\FeatureToggles\ORM;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Trompette\FeatureToggles\DBAL\SchemaConfigurator;

final class SchemaConfigurationListener
{
    /**
     * @var SchemaConfigurator[]
     */
    private array $configurators;

    public function __construct(SchemaConfigurator ...$configurators)
    {
        $this->configurators = $configurators;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $schema = $eventArgs->getSchema();
        $connection = $eventArgs->getEntityManager()->getConnection();

        foreach ($this->configurators as $configurator) {
            $configurator->configureSchema($schema, $connection);
        }
    }
}
