<?php

namespace Trompette\FeatureToggles;

interface TogglingStrategy
{
    public function decideIfTargetHasFeature(string $target, string $feature): bool;
}
