<?php

namespace Trompette\FeatureToggles\WhitelistStrategy;

interface ConfigurationRepository
{
    public function getWhitelistedTargets(string $feature): array;

    public function addToWhitelist(string $target, string $feature): void;

    public function removeFromWhitelist(string $target, string $feature): void;
}
