<?php

namespace Test\Trompette\FeatureToggles\OnOffStrategy;

use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\OnOffStrategy\ConfigurationRepository;
use Trompette\FeatureToggles\OnOffStrategy\OnOff;

class OnOffTest extends TestCase
{
    public function testTargetDoesNotHaveFeatureWhenNotEnabled()
    {
        $onOff = $this->configureEnabled('feature', false);

        $this->assertFalse($onOff->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenEnabled()
    {
        $onOff = $this->configureEnabled('feature', 100);

        $this->assertTrue($onOff->decideIfTargetHasFeature('target', 'feature'));
    }

    private function configureEnabled(string $feature, bool $enabled): OnOff
    {
        $configurationRepository = $this->prophesize(ConfigurationRepository::class);
        $configurationRepository->isEnabled($feature)->willReturn($enabled);

        return new OnOff($configurationRepository->reveal());
    }
}
