<?php

namespace Test\Trompette\FeatureToggles;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;

class FeatureRegistryTest extends TestCase
{
    public function testFeatureCanBeRegistered(): void
    {
        $registry = new FeatureRegistry();
        $registry->register(new FeatureDefinition('feature', 'awesome feature', 'strategy'));

        static::assertTrue($registry->exists('feature'));
    }

    public function testFeatureCannotBeRegisteredTwice(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $registry = new FeatureRegistry();
        $registry->register(new FeatureDefinition('feature', 'awesome feature', 'strategy'));
        $registry->register(new FeatureDefinition('feature', 'awesome feature', 'strategy'));
    }

    public function testGetDefinitionsKeyedByFeatureName(): void
    {
        $registry = new FeatureRegistry();
        $registry->register(new FeatureDefinition('feature_1', 'awesome feature', 'strategy'));
        $registry->register(new FeatureDefinition('feature_2', 'another awesome feature', 'strategy'));

        $definitions = $registry->getDefinitions();

        static::assertContainsOnlyInstancesOf(FeatureDefinition::class, $definitions);
        static::assertSame(['feature_1', 'feature_2'], \array_keys($definitions));
    }
}
