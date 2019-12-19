<?php

namespace Trompette\FeatureToggles\WhitelistStrategy;

use Trompette\FeatureToggles\TogglingStrategy;

class Whitelist implements TogglingStrategy
{
    /** @var ConfigurationRepository */
    private $configurationRepository;

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
}
