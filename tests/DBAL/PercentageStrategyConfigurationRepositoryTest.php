<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;

class PercentageStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTest
{
    protected function createRepository()
    {
        $this->repository = new PercentageStrategyConfigurationRepository($this->connection);
    }

    public function testConfigurationIsPersisted()
    {
        $this->repository->migrateSchema();
        static::assertSame(0, $this->repository->getPercentage('feature'));

        $this->repository->setPercentage(25, 'feature');
        static::assertSame(25, $this->repository->getPercentage('feature'));
    }
}
