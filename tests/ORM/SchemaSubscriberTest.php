<?php

namespace Test\Trompette\FeatureToggles\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Trompette\FeatureToggles\DBAL\SchemaConfigurator;
use Trompette\FeatureToggles\ORM\SchemaSubscriber;

class SchemaSubscriberTest extends TestCase
{
    use ProphecyTrait;

    public function testSchemaIsConfiguredAfterGeneration()
    {
        $schema = new Schema();
        $connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);

        $configurator = $this->prophesize(SchemaConfigurator::class);
        $configurator->configureSchema($schema, $connection)->shouldBeCalled();

        $connection->getEventManager()->addEventSubscriber(new SchemaSubscriber($configurator->reveal()));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()->willReturn($connection);

        $connection->getEventManager()->dispatchEvent(
            ToolEvents::postGenerateSchema,
            new GenerateSchemaEventArgs($entityManager->reveal(), $schema)
        );
    }
}
