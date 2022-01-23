<?php

namespace Test\Trompette\FeatureToggles\PercentageStrategy;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Trompette\FeatureToggles\PercentageStrategy\ConfigurationRepository;
use Trompette\FeatureToggles\PercentageStrategy\Percentage;

class PercentageTest extends TestCase
{
    use ProphecyTrait;

    public function testConfigurationCanBeRetrieved(): void
    {
        $percentage = $this->configurePercentage('feature', 25);

        static::assertSame(['percentage' => 25], $percentage->getConfiguration('feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenPercentageIs0(): void
    {
        $percentage = $this->configurePercentage('feature', 0);

        static::assertFalse($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenPercentageIs100(): void
    {
        $percentage = $this->configurePercentage('feature', 100);

        static::assertTrue($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetHasFeatureWhenPercentageIsAboveComputedHash(): void
    {
        $percentage = $this->configurePercentage('feature', 58);

        static::assertTrue($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testTargetDoesNotHaveFeatureWhenPercentageIsBelowComputedHash(): void
    {
        $percentage = $this->configurePercentage('feature', 56);

        static::assertFalse($percentage->decideIfTargetHasFeature('target', 'feature'));
    }

    public function testThatPercentageCannotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $percentage = $this->configurePercentage('feature', 0);
        $percentage->slide(-1, 'feature');
    }

    public function testThatPercentageCannotBeHigherThan100(): void
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
