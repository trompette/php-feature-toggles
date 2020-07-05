<?php

namespace Test\Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Tester\CommandTester;
use Trompette\FeatureToggles\Console\MigrateDBALSchemaCommand;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\DBAL\SchemaMigrator;

class MigrateDBALSchemaCommandTest extends TestCase
{
    public function testCommandCanBeExecuted()
    {
        $schemaMigrator = $this->prophesize(SchemaMigrator::class);
        $schemaMigrator->migrateSchema()->shouldBeCalled();

        $commandTester = new CommandTester(new MigrateDBALSchemaCommand($schemaMigrator->reveal()));
        $commandTester->execute($input = []);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('All done!', $commandTester->getDisplay());
    }
}