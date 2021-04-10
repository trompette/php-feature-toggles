<?php

namespace Test\Trompette\FeatureToggles\Console;

use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Trompette\FeatureToggles\Console\ConfigureFeatureCommand;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\ToggleRouter;

class ConfigureFeatureCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testCommandCanBeExecutedWithoutExtraParameters()
    {
        $toggleRouter = $this->prophesize(ToggleRouter::class);
        $toggleRouter->configureFeature('f', 's', 'm', [])->shouldBeCalled();

        $commandTester = new CommandTester(new ConfigureFeatureCommand($toggleRouter->reveal()));
        $commandTester->execute($input = [
            'feature' => 'f',
            'strategy' => 's',
            'method' => 'm',
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Feature f configured!', $commandTester->getDisplay());
    }

    public function testCommandCanBeExecutedWithExtraParameters()
    {
        $toggleRouter = $this->prophesize(ToggleRouter::class);
        $toggleRouter->configureFeature('f', 's', 'm', ['p1', 'p2'])->shouldBeCalled();

        $commandTester = new CommandTester(new ConfigureFeatureCommand($toggleRouter->reveal()));
        $commandTester->execute($input = [
            'feature' => 'f',
            'strategy' => 's',
            'method' => 'm',
            'parameters' => ['p1', 'p2'],
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Feature f configured!', $commandTester->getDisplay());
    }
}
