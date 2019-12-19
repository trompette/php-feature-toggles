<?php

namespace Test\Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Trompette\FeatureToggles\Console\ShowFeatureConfigurationCommand;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\ToggleRouter;

class ShowFeatureConfigurationCommandTest extends TestCase
{
    public function testCommandCanBeExecutedWithAFeature()
    {
        $commandTester = new CommandTester($this->configureCommand($withTarget = false));
        $commandTester->execute(['feature' => 'feature']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Feature', $commandTester->getDisplay());
        $this->assertStringContainsString('Configuration', $commandTester->getDisplay());
        $this->assertStringNotContainsString('Target', $commandTester->getDisplay());
    }

    public function testCommandCanBeExecutedWithAFeatureAndATarget()
    {
        $commandTester = new CommandTester($this->configureCommand($withTarget = true));
        $commandTester->execute(['feature' => 'feature', 'target' => 'target']);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringContainsString('Target', $commandTester->getDisplay());
    }

    private function configureCommand(bool $withTarget): ShowFeatureConfigurationCommand
    {
        $featureRegistry = new FeatureRegistry();
        $featureRegistry->register(new FeatureDefinition('feature', 'awesome feature', 'dummy'));

        $toggleRouter = $this->prophesize(ToggleRouter::class);
        $toggleRouter->getFeatureConfiguration('feature')->willReturn(['dummy' => ['paramKey' => 'paramValue']]);
        if ($withTarget) {
            $toggleRouter->hasFeature('target', 'feature')->willReturn(true);
        }

        $command = new ShowFeatureConfigurationCommand($featureRegistry, $toggleRouter->reveal());
        $command->setApplication(new Application());

        return $command;
    }
}
