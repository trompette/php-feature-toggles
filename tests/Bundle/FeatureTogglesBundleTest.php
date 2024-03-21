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
use Trompette\FeatureToggles\ToggleRouterInterface;

class FeatureTogglesBundleTest extends TestCase
{
    public function testAppKernelCanBootWithBundleRegistered(): void
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        static::assertTrue($container->has(ToggleRouter::class));
        static::assertTrue($container->has(ToggleRouterInterface::class));

        $DBALConnection = $container->get('my_doctrine_dbal_connection');
        static::assertInstanceOf(Connection::class, $DBALConnection);

        (new OnOffStrategyConfigurationRepository($DBALConnection))->migrateSchema();
        (new WhitelistStrategyConfigurationRepository($DBALConnection))->migrateSchema();
        (new PercentageStrategyConfigurationRepository($DBALConnection))->migrateSchema();

        $toggleRouter = $container->get(ToggleRouter::class);
        static::assertInstanceOf(ToggleRouter::class, $toggleRouter);
        static::assertIsArray($toggleRouter->getFeatureConfiguration('feature'));

        $toggleRouter->configureFeature('feature', 'onoff', 'on');
        static::assertTrue($toggleRouter->hasFeature('target', 'feature'));
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
                ->setArguments([['driver' => 'pdo_sqlite', 'memory' => true]])
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
