<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;

/**
 * @property PercentageStrategyConfigurationRepository $repository
 */
class PercentageStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTestCase
{
    protected function createRepository(): void
    {
        $this->repository = new PercentageStrategyConfigurationRepository($this->connection);
    }

    public function testConfigurationIsPersisted(): void
    {
        $this->repository->migrateSchema();
        static::assertSame(0, $this->repository->getPercentage('feature'));

        $this->repository->setPercentage(25, 'feature');
        static::assertSame(25, $this->repository->getPercentage('feature'));
    }

    public function testListAndRemoveFeatures(): void
    {
        $this->repository->migrateSchema();
        static::assertEmpty($this->repository->listFeatures());

        $this->repository->setPercentage(10, 'feature_1');
        $this->repository->setPercentage(50, 'feature_2');
        static::assertSame(['feature_1', 'feature_2'], $this->repository->listFeatures());

        $this->repository->removeFeature('feature_1');
        static::assertSame(['feature_2'], $this->repository->listFeatures());
    }
}
