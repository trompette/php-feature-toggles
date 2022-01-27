<?php

namespace Trompette\FeatureToggles;

interface TogglingStrategy
{
    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(string $feature): array;

    public function decideIfTargetHasFeature(string $target, string $feature): bool;
}
