<?php

namespace Test\Trompette\FeatureToggles\OnOffStrategy;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Trompette\FeatureToggles\OnOffStrategy\ConfigurationRepository;
use Trompette\FeatureToggles\OnOffStrategy\OnOff;

class OnOffTest extends TestCase
{
    use ProphecyTrait;

    public function testConfigurationCanBeRetrieved()
    {
        $onOff = $this->configureOnOff('feature', true);

        $this->assertSame(['enabled' => true], $onOff->getConfiguration('feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenNotEnabled()
    {
        $onOff = $this->configureOnOff('feature', false);

        $this->assertFalse($onOff->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenEnabled()
    {
        $onOff = $this->configureOnOff('feature', true);

        $this->assertTrue($onOff->decideIfTargetHasFeature('target', 'feature'));
    }

    private function configureOnOff(string $feature, bool $enabled): OnOff
    {
        $configurationRepository = $this->prophesize(ConfigurationRepository::class);
        $configurationRepository->isEnabled($feature)->willReturn($enabled);

        return new OnOff($configurationRepository->reveal());
    }
}
