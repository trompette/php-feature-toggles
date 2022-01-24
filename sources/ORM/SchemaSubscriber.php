<?php

namespace Trompette\FeatureToggles\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Trompette\FeatureToggles\DBAL\SchemaConfigurator;

class SchemaSubscriber implements EventSubscriber
{
    /** @var SchemaConfigurator[] */
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

    public function getSubscribedEvents(): array
    {
        // subscribe to event only if Doctrine ORM is installed
        return class_exists(ToolEvents::class) ? [ToolEvents::postGenerateSchema] : [];
    }
}
