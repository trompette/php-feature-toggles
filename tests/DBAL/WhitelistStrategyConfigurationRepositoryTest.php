<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;

/**
 * @property WhitelistStrategyConfigurationRepository $repository
 */
class WhitelistStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTestCase
{
    protected function createRepository(): void
    {
        $this->repository = new WhitelistStrategyConfigurationRepository($this->connection);
    }

    public function testConfigurationIsPersisted(): void
    {
        $this->repository->migrateSchema();
        static::assertEmpty($this->repository->getWhitelistedTargets('feature'));

        $this->repository->addToWhitelist('target', 'feature');
        static::assertSame(['target'], $this->repository->getWhitelistedTargets('feature'));

        $this->repository->removeFromWhitelist('target', 'feature');
        static::assertEmpty($this->repository->getWhitelistedTargets('feature'));
    }

    public function testListAndRemoveFeatures(): void
    {
        $this->repository->migrateSchema();
        static::assertEmpty($this->repository->listFeatures());

        $this->repository->addToWhitelist('target_a', 'feature_1');
        $this->repository->addToWhitelist('target_a', 'feature_2');
        $this->repository->addToWhitelist('target_b', 'feature_2');
        static::assertSame(['feature_1', 'feature_2'], $this->repository->listFeatures());

        $this->repository->removeFeature('feature_1');
        static::assertSame(['feature_2'], $this->repository->listFeatures());
    }
}
