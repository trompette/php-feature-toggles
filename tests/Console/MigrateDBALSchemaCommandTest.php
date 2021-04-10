<?php

namespace Test\Trompette\FeatureToggles\Console;

use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Trompette\FeatureToggles\Console\MigrateDBALSchemaCommand;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\DBAL\SchemaMigrator;

class MigrateDBALSchemaCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testCommandCanBeExecuted()
    {
        $schemaMigrator = $this->prophesize(SchemaMigrator::class);
        $schemaMigrator->migrateSchema()->shouldBeCalled();

        $commandTester = new CommandTester(new MigrateDBALSchemaCommand($schemaMigrator->reveal()));
        $commandTester->execute($input = []);

        static::assertSame(0, $commandTester->getStatusCode());
        static::assertStringContainsString('All done!', $commandTester->getDisplay());
    }
}
