<?php

namespace Trompette\FeatureToggles;

use Assert\Assert;

class FeatureRegistry
{
    /**
     * @var array<string, FeatureDefinition>
     */
    private array $definitions = [];

    public function register(FeatureDefinition $definition): void
    {
        $feature = $definition->getName();

        Assert::that($this->exists($feature))->false("$feature is already registered");

        $this->definitions[$feature] = $definition;
    }

    public function registerFeature(string $name, string $description, string $strategy): void
    {
        $this->register(new FeatureDefinition($name, $description, $strategy));
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
}
