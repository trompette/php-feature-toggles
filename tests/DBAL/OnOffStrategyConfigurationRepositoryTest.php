<?php

namespace Test\Trompette\FeatureToggles\DBAL;

use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;

class OnOffStrategyConfigurationRepositoryTest extends ConfigurationRepositoryTest
{
    protected function createRepository()
    {
        $this->repository = new OnOffStrategyConfigurationRepository($this->connection);
    }

    public function testConfigurationIsPersisted()
    {
        $this->repository->migrateSchema();
        static::assertFalse($this->repository->isEnabled('feature'));

        $this->repository->setEnabled(true, 'feature');
        static::assertTrue($this->repository->isEnabled('feature'));

        $this->repository->setEnabled(false, 'feature');
        static::assertFalse($this->repository->isEnabled('feature'));
    }
}
