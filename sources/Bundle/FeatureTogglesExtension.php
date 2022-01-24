<?php

namespace Trompette\FeatureToggles\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Trompette\FeatureToggles\Console\ConfigureFeatureCommand;
use Trompette\FeatureToggles\Console\MigrateDBALSchemaCommand;
use Trompette\FeatureToggles\Console\ShowFeatureConfigurationCommand;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\OnOffStrategy\OnOff;
use Trompette\FeatureToggles\ORM\SchemaSubscriber;
use Trompette\FeatureToggles\PercentageStrategy\Percentage;
use Trompette\FeatureToggles\ToggleRouter;
use Trompette\FeatureToggles\WhitelistStrategy\Whitelist;

class FeatureTogglesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new FeatureTogglesConfiguration(), $configs);

        $this->defineFeatureRegistry($config['declared_features'], $container);
        $this->defineTogglingStrategies($config['doctrine_dbal_connection'], $container);
        $this->defineToggleRouter($container);
        $this->defineConsoleCommands($container);
        $this->defineDoctrineEventSubscriber($container);
    }

    private function defineFeatureRegistry(array $declaredFeatures, ContainerBuilder $container): void
    {
        $featureRegistry = $container->register(FeatureRegistry::class, FeatureRegistry::class);

        foreach ($declaredFeatures as $name => $declaredFeature) {
            $featureRegistry->addMethodCall(
                'registerFeature',
                [$name, $declaredFeature['description'], $declaredFeature['strategy']]
            );
        }
    }

    private function defineTogglingStrategies(string $doctrineDBALConnection, ContainerBuilder $container): void
    {
        $configurationRepositories = [
            'onoff' => OnOffStrategyConfigurationRepository::class,
            'whitelist' => WhitelistStrategyConfigurationRepository::class,
            'percentage' => PercentageStrategyConfigurationRepository::class,
        ];

        foreach ($configurationRepositories as $key => $class) {
            $container->register($class, $class)->addArgument(new Reference($doctrineDBALConnection));
        }

        $togglingStrategies = [
            'onoff' => OnOff::class,
            'whitelist' => Whitelist::class,
            'percentage' => Percentage::class,
        ];

        foreach ($togglingStrategies as $key => $class) {
            $container->register($class, $class)->addArgument(new Reference($configurationRepositories[$key]));
        }
    }

    private function defineToggleRouter(ContainerBuilder $container): void
    {
        $definition = $container
            ->register(ToggleRouter::class, ToggleRouter::class)
            ->setPublic(true)
            ->addArgument(new Reference(FeatureRegistry::class))
            ->addArgument([
                'onoff' => new Reference(OnOff::class),
                'whitelist' => new Reference(Whitelist::class),
                'percentage' => new Reference(Percentage::class),
            ])
        ;

        if ($container->has('logger')) {
            $definition
                ->addMethodCall('setLogger', [new Reference('logger')])
                ->addTag('monolog.logger', ['channel' => 'feature_toggles'])
            ;
        }
    }

    private function defineConsoleCommands(ContainerBuilder $container): void
    {
        $container
            ->register(MigrateDBALSchemaCommand::class, MigrateDBALSchemaCommand::class)
            ->addArgument(new Reference(OnOffStrategyConfigurationRepository::class))
            ->addArgument(new Reference(WhitelistStrategyConfigurationRepository::class))
            ->addArgument(new Reference(PercentageStrategyConfigurationRepository::class))
            ->addTag('console.command')
        ;

        $container
            ->register(ShowFeatureConfigurationCommand::class, ShowFeatureConfigurationCommand::class)
            ->addArgument(new Reference(FeatureRegistry::class))
            ->addArgument(new Reference(ToggleRouter::class))
            ->addTag('console.command')
        ;

        $container
            ->register(ConfigureFeatureCommand::class, ConfigureFeatureCommand::class)
            ->addArgument(new Reference(ToggleRouter::class))
            ->addTag('console.command')
        ;
    }

    private function defineDoctrineEventSubscriber(ContainerBuilder $container): void
    {
        $container
            ->register(SchemaSubscriber::class, SchemaSubscriber::class)
            ->addArgument(new Reference(OnOffStrategyConfigurationRepository::class))
            ->addArgument(new Reference(WhitelistStrategyConfigurationRepository::class))
            ->addArgument(new Reference(PercentageStrategyConfigurationRepository::class))
            ->addTag('doctrine.event_subscriber')
        ;
    }
}
