<?php

namespace Trompette\FeatureToggles\OnOffStrategy;

interface ConfigurationRepository
{
    public function isEnabled(string $feature): bool;

    public function setEnabled(bool $enabled, string $feature): void;
}
