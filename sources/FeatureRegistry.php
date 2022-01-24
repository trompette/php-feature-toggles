<?php

namespace Trompette\FeatureToggles;

use Assert\Assert;

class FeatureRegistry
{
    /** @var FeatureDefinition[] */
    private array $definitions = [];

    public function register(): void
    {
        $definition = self::inferDefinition(func_get_args());
        $feature = $definition->getName();

        Assert::that($this->exists($feature))->false("$feature is already registered");

        $this->definitions[$feature] = $definition;
    }

    public function exists(string $feature): bool
    {
        return array_key_exists($feature, $this->definitions);
    }

    public function getDefinition(string $feature): FeatureDefinition
    {
        Assert::that($this->exists($feature))->true("$feature does not exist");

        return $this->definitions[$feature];
    }

    private static function inferDefinition(array $args): FeatureDefinition
    {
        switch (count($args)) {
            case 1: $definition = $args[0]; break;
            case 3: $definition = new FeatureDefinition(...$args); break;
            default: throw new \ArgumentCountError("invalid register() call");
        }

        Assert::that($definition)->isInstanceOf(FeatureDefinition::class);

        return $definition;
    }
}
