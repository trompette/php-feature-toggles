<?php

namespace Trompette\FeatureToggles\OnOffStrategy;

use Trompette\FeatureToggles\TogglingStrategy;

class OnOff implements TogglingStrategy
{
    /** @var ConfigurationRepository */
    private $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
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
}
