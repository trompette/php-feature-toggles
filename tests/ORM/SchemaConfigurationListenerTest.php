<?php

namespace Test\Trompette\FeatureToggles\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Trompette\FeatureToggles\DBAL\SchemaConfigurator;
use Trompette\FeatureToggles\ORM\SchemaConfigurationListener;

class SchemaConfigurationListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testSchemaIsConfiguredAfterGeneration(): void
    {
        $schema = new Schema();
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->willReturn($connection);

        $configurator = $this->prophesize(SchemaConfigurator::class);
        $configurator->configureSchema($schema, $connection)->shouldBeCalled();

        $eventManager = new EventManager();
        $eventManager->addEventListener(
            ToolEvents::postGenerateSchema,
            new SchemaConfigurationListener($configurator->reveal())
        );
        $eventManager->dispatchEvent(
            ToolEvents::postGenerateSchema,
            new GenerateSchemaEventArgs($entityManager->reveal(), $schema)
        );
    }
}
