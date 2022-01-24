<?php

namespace Trompette\FeatureToggles\WhitelistStrategy;

interface ConfigurationRepository
{
    /**
     * @return string[]
     */
    public function getWhitelistedTargets(string $feature): array;

    public function addToWhitelist(string $target, string $feature): void;

    public function removeFromWhitelist(string $target, string $feature): void;
}
