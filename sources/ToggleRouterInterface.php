<?php

namespace Trompette\FeatureToggles;

interface ToggleRouterInterface
{
    public function hasFeature(string $target, string $feature): bool;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getFeatureConfiguration(string $feature): array;

    /**
     * @param mixed $parameters
     */
    public function configureFeature(string $feature, string $strategy, string $method, $parameters = []): void;

    /**
     * @return string[]
     */
    public function listUnregisteredFeatures(): array;

    public function clearFeatureConfiguration(string $feature): void;
}
