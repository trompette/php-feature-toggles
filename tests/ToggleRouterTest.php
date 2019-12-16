<?php

namespace Test\Trompette\FeatureToggles;

use Assert\InvalidArgumentException;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\OnOffStrategy\OnOff;
use Trompette\FeatureToggles\PercentageStrategy\Percentage;
use Trompette\FeatureToggles\ToggleRouter;
use Trompette\FeatureToggles\TogglingStrategy;
use Trompette\FeatureToggles\WhitelistStrategy\Whitelist;

class ToggleRouterTest extends TestCase
{
    public function testTargetDoesNotHaveUnregisteredFeature()
    {
        $router = $this->configureToggleRouter();

        $this->assertFalse($router->hasFeature('target', 'feature'));
    }

    public function testTargetHasRegisteredFeatureWithValidStrategy()
    {
        $router = $this->configureToggleRouter(new FeatureDefinition('feature', 'awesome feature', 'true'));

        $this->assertTrue($router->hasFeature('target', 'feature'));
    }

    public function testTargetDoesNotHaveRegisteredFeatureWithValidStrategy()
    {
        $router = $this->configureToggleRouter(new FeatureDefinition('feature', 'awesome feature', 'false'));

        $this->assertFalse($router->hasFeature('target', 'feature'));
    }

    public function testTargetDoesNotHaveRegisteredFeatureWithInvalidStrategy()
    {
        $router = $this->configureToggleRouter(new FeatureDefinition('feature', 'awesome feature', 'invalid'));

        $this->assertFalse($router->hasFeature('target', 'feature'));
    }

    public function testUnregisteredFeatureCanBeConfiguredByStrategy()
    {
        $this->expectExceptionMessage("configure('value', 'feature') not implemented");

        $router = $this->configureToggleRouter(null, ['dummy' => new DummyStrategy()]);
        $router->configureFeature('feature', 'dummy', 'configure', 'value');
    }

    public function testRegisteredFeatureCanBeConfiguredByStrategy()
    {
        $this->expectExceptionMessage("configure('value', 'feature') not implemented");

        $router = $this->configureToggleRouter(
            new FeatureDefinition('feature', 'awesome feature', 'true'),
            ['dummy' => new DummyStrategy()]
        );
        $router->configureFeature('feature', 'dummy', 'configure', 'value');
    }

    public function testFeatureCannotBeConfiguredWhenStrategyDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);

        $router = $this->configureToggleRouter();
        $router->configureFeature('feature', 'invalid', 'configure');
    }

    public function testFeatureCannotBeConfiguredWhenMethodDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);

        $router = $this->configureToggleRouter(null, ['dummy' => new DummyStrategy()]);
        $router->configureFeature('feature', 'dummy', 'absent');
    }

    public function testStrategiesCanBeCombinedWithBooleanOperators()
    {
        $router = $this->configureToggleRouter(
            new FeatureDefinition('feature', 'awesome feature', 'onoff or whitelist or percentage'),
            $this->configureAllStrategies()
        );

        $this->assertFalse($router->hasFeature('target', 'feature'));

        $router->configureFeature('feature', 'onoff', 'on');

        $this->assertTrue($router->hasFeature('target', 'feature'));

        $router->configureFeature('feature', 'onoff', 'off');
        $router->configureFeature('feature', 'whitelist', 'allow', 'target');

        $this->assertTrue($router->hasFeature('target', 'feature'));

        $router->configureFeature('feature', 'whitelist', 'disallow', 'target');
        $router->configureFeature('feature', 'percentage', 'slide', 56);

        $this->assertFalse($router->hasFeature('target', 'feature'));

        $router->configureFeature('feature', 'percentage', 'slide', 58);

        $this->assertTrue($router->hasFeature('target', 'feature'));
    }

    private function configureToggleRouter(FeatureDefinition $definition = null, array $strategies = []): ToggleRouter
    {
        $registry = new FeatureRegistry();

        if ($definition) {
            $registry->register($definition);
        }

        return new ToggleRouter($registry, $strategies);
    }

    private function configureAllStrategies(): array
    {
        $DBALConnection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);

        $onOffConfigurationRepository = new OnOffStrategyConfigurationRepository($DBALConnection);
        $onOffConfigurationRepository->migrateSchema();
        $whitelistConfigurationRepository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $whitelistConfigurationRepository->migrateSchema();
        $percentageConfigurationRepository = new PercentageStrategyConfigurationRepository($DBALConnection);
        $percentageConfigurationRepository->migrateSchema();

        return [
            'onoff' => new OnOff($onOffConfigurationRepository),
            'whitelist' => new Whitelist($whitelistConfigurationRepository),
            'percentage' => new Percentage($percentageConfigurationRepository),
        ];
    }
}

class DummyStrategy implements TogglingStrategy
{
    public function decideIfTargetHasFeature(string $target, string $feature): bool
    {
        return true;
    }

    public function configure(string $value, string $feature): void
    {
        throw new \Exception(__METHOD__."('$value', '$feature') not implemented");
    }
}
