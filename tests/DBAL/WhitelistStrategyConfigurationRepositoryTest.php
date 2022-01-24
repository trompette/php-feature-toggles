<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;

/**
 * @property WhitelistStrategyConfigurationRepository $repository
 */
class WhitelistStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTest
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
}
