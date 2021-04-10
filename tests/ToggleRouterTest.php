<?php

namespace Test\Trompette\FeatureToggles;

use Assert\InvalidArgumentException;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\Test\TestLogger;
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
    use ProphecyTrait;

    public function testTargetDoesNotHaveUnregisteredFeature()
    {
        $router = $this->configureToggleRouter();
        $router->setLogger($logger = new TestLogger());

        $this->assertFalse($router->hasFeature('target', 'feature'));
        $this->assertTrue($logger->hasWarning('Feature is unregistered'));
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
        $router->setLogger($logger = new TestLogger());

        $this->assertFalse($router->hasFeature('target', 'feature'));
        $this->assertTrue($logger->hasWarning('Feature strategy is invalid'));
    }

    public function testFeatureConfigurationCanBeRetrievedByStrategy()
    {
        $strategy = $this->prophesize(FakeStrategy::class);
        $strategy->getConfiguration('feature')->willReturn(['key' => 'value']);

        $router = $this->configureToggleRouter(null, ['fake' => $strategy->reveal()]);

        $this->assertSame(['fake' => ['key' => 'value']], $router->getFeatureConfiguration('feature'));
    }

    public function testUnregisteredFeatureCanBeConfiguredByStrategy()
    {
        $strategy = $this->prophesize(FakeStrategy::class);
        $strategy->configure('value', 'feature')->shouldBeCalled();

        $router = $this->configureToggleRouter(null, ['fake' => $strategy->reveal()]);
        $router->setLogger($logger = new TestLogger());
        $router->configureFeature('feature', 'fake', 'configure', 'value');

        $this->assertTrue($logger->hasInfo('Feature has been configured'));
    }

    public function testRegisteredFeatureCanBeConfiguredByStrategy()
    {
        $strategy = $this->prophesize(FakeStrategy::class);
        $strategy->configure('value', 'feature')->shouldBeCalled();

        $router = $this->configureToggleRouter(
            new FeatureDefinition('feature', 'awesome feature', 'true'),
            ['fake' => $strategy->reveal()]
        );
        $router->setLogger($logger = new TestLogger());
        $router->configureFeature('feature', 'fake', 'configure', 'value');

        $this->assertTrue($logger->hasInfo('Feature has been configured'));
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

        $strategy = $this->prophesize(FakeStrategy::class);

        $router = $this->configureToggleRouter(null, ['fake' => $strategy->reveal()]);
        $router->configureFeature('feature', 'fake', 'absent');
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

interface FakeStrategy extends TogglingStrategy
{
    public function configure(string $value, string $feature): void;
}
