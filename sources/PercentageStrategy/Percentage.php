<?php

namespace Trompette\FeatureToggles\PercentageStrategy;

use Trompette\FeatureToggles\TogglingStrategy;

class Percentage implements TogglingStrategy
{
    /** @var ConfigurationRepository */
    private $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function decideIfTargetHasFeature(string $target, string $feature): bool
    {
        $percentage = $this->configurationRepository->getPercentage($feature);

        if ($percentage < 1) {
            return false;
        }

        if ($percentage > 99) {
            return true;
        }

        return $this->computeHash($target, $feature) < $percentage;
    }

    public function slide(int $percentage, string $feature): void
    {
        $this->configurationRepository->setPercentage($percentage, $feature);
    }

    private function computeHash(string $raw, string $salt): int
    {
        return hexdec(substr(md5($raw . $salt), 0, 8)) % 100;
    }
}
