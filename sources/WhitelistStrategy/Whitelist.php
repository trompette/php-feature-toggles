<?php

namespace Trompette\FeatureToggles\WhitelistStrategy;

use Trompette\FeatureToggles\TogglingStrategy;

final class Whitelist implements TogglingStrategy
{
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getConfiguration(string $feature): array
    {
        return ['whitelistedTargets' => $this->configurationRepository->getWhitelistedTargets($feature)];
    }

    public function decideIfTargetHasFeature(string $target, string $feature): bool
    {
        $whitelistedTargets = $this->configurationRepository->getWhitelistedTargets($feature);

        return in_array($target, $whitelistedTargets);
    }

    public function allow(string $target, string $feature): void
    {
        $this->configurationRepository->addToWhitelist($target, $feature);
    }

    public function disallow(string $target, string $feature): void
    {
        $this->configurationRepository->removeFromWhitelist($target, $feature);
    }

    public function listFeatures(): array
    {
        return $this->configurationRepository->listFeatures();
    }

    public function clearFeatureConfiguration(string $feature): void
    {
        $this->configurationRepository->removeFeature($feature);
    }
}
