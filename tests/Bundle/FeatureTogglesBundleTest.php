<?php

namespace Test\Trompette\FeatureToggles\Bundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Trompette\FeatureToggles\Bundle\FeatureTogglesBundle;
use Trompette\FeatureToggles\ToggleRouter;

class FeatureTogglesBundleTest extends TestCase
{
    public function testAppKernelCanBootWithBundleRegistered()
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

        $this->assertTrue($kernel->getContainer()->has(ToggleRouter::class));
    }
}

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [new FeatureTogglesBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container
                ->register('my_doctrine_dbal_connection', Connection::class)
                ->setFactory([DriverManager::class, 'getConnection'])
                ->setArguments([['url' => 'sqlite:///:memory:']])
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

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/php-feature-toggles/'.Kernel::VERSION.'/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/php-feature-toggles/'.Kernel::VERSION.'/logs';
    }
}
