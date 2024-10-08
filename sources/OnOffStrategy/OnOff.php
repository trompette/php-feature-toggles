<?php

namespace Trompette\FeatureToggles\OnOffStrategy;

use Trompette\FeatureToggles\TogglingStrategy;

final class OnOff implements TogglingStrategy
{
    private ConfigurationRepository $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getConfiguration(string $feature): array
    {
        return ['enabled' => $this->configurationRepository->isEnabled($feature)];
    }

    public function decideIfTargetHasFeature(string $target, string $feature): bool
    {
        return $this->configurationRepository->isEnabled($feature);
    }

    public function on(string $feature): void
    {
        $this->configurationRepository->setEnabled(true, $feature);
    }

    public function off(string $feature): void
    {
        $this->configurationRepository->setEnabled(false, $feature);
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
