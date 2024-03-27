<?php

namespace Test\Trompette\FeatureToggles\Console;

use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Trompette\FeatureToggles\Console\ClearUnregisteredFeaturesCommand;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\ToggleRouterInterface;

class ClearUnregisteredFeaturesCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testCommandCanBeExecuted(): void
    {
        $toggleRouter = $this->prophesize(ToggleRouterInterface::class);
        $toggleRouter->listUnregisteredFeatures()->willReturn(['feature_1', 'feature_2']);
        $toggleRouter->clearFeatureConfiguration('feature_1')->shouldBeCalled();
        $toggleRouter->clearFeatureConfiguration('feature_2')->shouldBeCalled();

        $commandTester = new CommandTester(new ClearUnregisteredFeaturesCommand($toggleRouter->reveal()));
        $commandTester->execute($input = []);

        static::assertSame(0, $commandTester->getStatusCode());
        static::assertStringContainsString('2 unregistered feature(s) have been found', $commandTester->getDisplay());
        static::assertStringContainsString('Successfully cleared configuration for 2 feature(s): feature_1, feature_2.', $commandTester->getDisplay());
    }
}
