<?php

namespace Test\Trompette\FeatureToggles;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\ToggleRouter;
use Trompette\FeatureToggles\TogglingStrategy;

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

    private function configureToggleRouter(FeatureDefinition $definition = null, array $strategies = []): ToggleRouter
    {
        $registry = new FeatureRegistry();

        if ($definition) {
            $registry->register($definition);
        }

        return new ToggleRouter($registry, $strategies);
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
