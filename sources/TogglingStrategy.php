<?php

namespace Trompette\FeatureToggles;

interface TogglingStrategy
{
    public function getConfiguration(string $feature): array;

    public function decideIfTargetHasFeature(string $target, string $feature): bool;
}
