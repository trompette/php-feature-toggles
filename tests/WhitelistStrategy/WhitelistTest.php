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

        $this->assertSame(['whitelistedTargets' => ['whitelisted']], $whitelist->getConfiguration('feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenWhitelistIsEmpty()
    {
        $whitelist = $this->configureWhitelist('feature', []);

        $this->assertFalse($whitelist->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenWhitelisted()
    {
        $whitelist = $this->configureWhitelist('feature', ['whitelisted']);

        $this->assertTrue($whitelist->decideIfTargetHasFeature('whitelisted', 'feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenNotWhitelisted()
    {
        $whitelist = $this->configureWhitelist('feature', ['whitelisted']);

        $this->assertFalse($whitelist->decideIfTargetHasFeature('not whitelisted', 'feature'));
    }

    private function configureWhitelist(string $feature, array $whitelistedTargets): Whitelist
    {
        $configurationRepository = $this->prophesize(ConfigurationRepository::class);
        $configurationRepository->getWhitelistedTargets($feature)->willReturn($whitelistedTargets);

        return new Whitelist($configurationRepository->reveal());
    }
}
