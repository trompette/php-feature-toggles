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
        $registry->register('other feature', 'awesome other feature', 'strategy');

        static::assertTrue($registry->exists('feature'));
        static::assertTrue($registry->exists('other feature'));
    }

    public function testFeatureCannotBeRegisteredTwice(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $registry = new FeatureRegistry();
        $registry->register(new FeatureDefinition('feature', 'awesome feature', 'strategy'));
        $registry->register(new FeatureDefinition('feature', 'awesome feature', 'strategy'));
    }
}
