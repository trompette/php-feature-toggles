<?php

namespace Trompette\FeatureToggles\PercentageStrategy;

interface ConfigurationRepository
{
    public function getPercentage(string $feature): int;

    public function setPercentage(int $percentage, string $feature): void;

    /**
     * @return string[]
     */
    public function listFeatures(): array;

    public function removeFeature(string $feature): void;
}
