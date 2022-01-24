<?php

namespace Test\Trompette\FeatureToggles\Bundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Trompette\FeatureToggles\Bundle\FeatureTogglesBundle;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use Trompette\FeatureToggles\ToggleRouter;

class FeatureTogglesBundleTest extends TestCase
{
    public function testAppKernelCanBootWithBundleRegistered(): void
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

        $DBALConnection = $kernel->getContainer()->get('my_doctrine_dbal_connection');
        static::assertInstanceOf(Connection::class, $DBALConnection);

        $onOffConfigurationRepository = new OnOffStrategyConfigurationRepository($DBALConnection);
        $onOffConfigurationRepository->migrateSchema();
        $whitelistConfigurationRepository = new WhitelistStrategyConfigurationRepository($DBALConnection);
        $whitelistConfigurationRepository->migrateSchema();
        $percentageConfigurationRepository = new PercentageStrategyConfigurationRepository($DBALConnection);
        $percentageConfigurationRepository->migrateSchema();

        $toggleRouter = $kernel->getContainer()->get(ToggleRouter::class);
        static::assertInstanceOf(ToggleRouter::class, $toggleRouter);
        static::assertIsArray($toggleRouter->getFeatureConfiguration('feature'));
    }
}

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [new FeatureTogglesBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container
                ->register('my_doctrine_dbal_connection', Connection::class)
                ->setFactory([DriverManager::class, 'getConnection'])
                ->setArguments([['url' => 'sqlite:///:memory:']])
                ->setPublic(true)
            ;

            $container->loadFromExtension('feature_toggles', [
                'doctrine_dbal_connection' => 'my_doctrine_dbal_connection',
                'declared_features' => [
                    'feature' => [
                        'description' => 'awesome feature',
                        'strategy' => 'onoff or whitelist or percentage',
                    ]
                ]
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/php-feature-toggles/'.Kernel::VERSION.'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/php-feature-toggles/'.Kernel::VERSION.'/logs';
    }
}
