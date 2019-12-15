<?php

namespace Trompette\FeatureToggles\PercentageStrategy;

interface ConfigurationRepository
{
    public function getPercentage(string $feature): int;

    public function setPercentage(int $percentage, string $feature): void;
}
