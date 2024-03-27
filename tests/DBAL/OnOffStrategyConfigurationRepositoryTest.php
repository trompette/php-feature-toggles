<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;

/**
 * @property OnOffStrategyConfigurationRepository $repository
 */
class OnOffStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTestCase
{
    protected function createRepository(): void
    {
        $this->repository = new OnOffStrategyConfigurationRepository($this->connection);
    }

    public function testConfigurationIsPersisted(): void
    {
        $this->repository->migrateSchema();
        static::assertFalse($this->repository->isEnabled('feature'));

        $this->repository->setEnabled(true, 'feature');
        static::assertTrue($this->repository->isEnabled('feature'));

        $this->repository->setEnabled(false, 'feature');
        static::assertFalse($this->repository->isEnabled('feature'));
    }

    public function testListAndRemoveFeatures(): void
    {
        $this->repository->migrateSchema();
        static::assertEmpty($this->repository->listFeatures());

        $this->repository->setEnabled(true, 'feature_1');
        $this->repository->setEnabled(false, 'feature_2');
        static::assertSame(['feature_1', 'feature_2'], $this->repository->listFeatures());

        $this->repository->removeFeature('feature_1');
        static::assertSame(['feature_2'], $this->repository->listFeatures());
    }
}
