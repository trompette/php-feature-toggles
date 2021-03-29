<?php

namespace Test\Trompette\FeatureToggles\PercentageStrategy;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\PercentageStrategy\ConfigurationRepository;
use Trompette\FeatureToggles\PercentageStrategy\Percentage;

class PercentageTest extends TestCase
{
    public function testConfigurationCanBeRetrieved()
    {
        $percentage = $this->configurePercentage('feature', 25);

        $this->assertSame(['percentage' => 25], $percentage->getConfiguration('feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenPercentageIs0()
    {
        $percentage = $this->configurePercentage('feature', 0);

        $this->assertFalse($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenPercentageIs100()
    {
        $percentage = $this->configurePercentage('feature', 100);

        $this->assertTrue($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenPercentageIsAboveComputedHash()
    {
        $percentage = $this->configurePercentage('feature', 58);

        $this->assertTrue($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenPercentageIsBelowComputedHash()
    {
        $percentage = $this->configurePercentage('feature', 56);

        $this->assertFalse($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testThatPercentageCannotBeNegative()
    {
        $this->expectException(InvalidArgumentException::class);

        $percentage = $this->configurePercentage('feature', 0);
        $percentage->slide(-1, 'feature');
    }

    public function testThatPercentageCannotBeHigherThan100()
    {
        $this->expectException(InvalidArgumentException::class);

        $percentage = $this->configurePercentage('feature', 0);
        $percentage->slide(101, 'feature');
    }

    private function configurePercentage(string $feature, int $percentage): Percentage
    {
        $configurationRepository = $this->prophesize(ConfigurationRepository::class);
        $configurationRepository->getPercentage($feature)->willReturn($percentage);

        return new Percentage($configurationRepository->reveal());
    }
}
