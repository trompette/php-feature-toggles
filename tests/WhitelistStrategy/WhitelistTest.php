<?php

namespace Test\Trompette\FeatureToggles\WhitelistStrategy;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Trompette\FeatureToggles\WhitelistStrategy\ConfigurationRepository;
use Trompette\FeatureToggles\WhitelistStrategy\Whitelist;

class WhitelistTest extends TestCase
{
    use ProphecyTrait;

    public function testConfigurationCanBeRetrieved()
    {
        $whitelist = $this->configureWhitelist('feature', ['whitelisted']);

        static::assertSame(['whitelistedTargets' => ['whitelisted']], $whitelist->getConfiguration('feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenWhitelistIsEmpty()
    {
        $whitelist = $this->configureWhitelist('feature', []);

        static::assertFalse($whitelist->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenWhitelisted()
    {
        $whitelist = $this->configureWhitelist('feature', ['whitelisted']);

        static::assertTrue($whitelist->decideIfTargetHasFeature('whitelisted', 'feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenNotWhitelisted()
    {
        $whitelist = $this->configureWhitelist('feature', ['whitelisted']);

        static::assertFalse($whitelist->decideIfTargetHasFeature('not whitelisted', 'feature'));
    }

    private function configureWhitelist(string $feature, array $whitelistedTargets): Whitelist
    {
        $configurationRepository = $this->prophesize(ConfigurationRepository::class);
        $configurationRepository->getWhitelistedTargets($feature)->willReturn($whitelistedTargets);

        return new Whitelist($configurationRepository->reveal());
    }
}
