<?php

namespace Trompette\FeatureToggles\Bundle;

use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Trompette\FeatureToggles\Console\ConfigureFeatureCommand;
use Trompette\FeatureToggles\Console\MigrateDBALSchemaCommand;
use Trompette\FeatureToggles\Console\ShowFeatureConfigurationCommand;
use Trompette\FeatureToggles\DBAL\OnOffStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\PercentageStrategyConfigurationRepository;
use Trompette\FeatureToggles\DBAL\WhitelistStrategyConfigurationRepository;
use Trompette\FeatureToggles\FeatureDefinition;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\OnOffStrategy\OnOff;
use Trompette\FeatureToggles\ORM\SchemaConfigurationListener;
use Trompette\FeatureToggles\PercentageStrategy\Percentage;
use Trompette\FeatureToggles\ToggleRouter;
use Trompette\FeatureToggles\ToggleRouterInterface;
use Trompette\FeatureToggles\WhitelistStrategy\Whitelist;

final class FeatureTogglesExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new FeatureTogglesConfiguration(), $configs);

        $this->defineFeatureRegistry($config['declared_features'], $container);
        $this->defineTogglingStrategies($config['doctrine_dbal_connection'], $container);
        $this->defineToggleRouter($container);
        $this->defineConsoleCommands($container);
        $this->defineDoctrineEventListener($container);
    }

    /**
     * @param array<string, array{description: string, strategy: string}> $declaredFeatures
     */
    private function defineFeatureRegistry(array $declaredFeatures, ContainerBuilder $container): void
    {
        $featureRegistry = $container->register(FeatureRegistry::class, FeatureRegistry::class);

        foreach ($declaredFeatures as $name => ['description' => $description, 'strategy' => $strategy]) {
            $id = sprintf('declared_features.%s', $name);
            $container->register($id, FeatureDefinition::class)->setArguments([$name, $description, $strategy]);
            $featureRegistry->addMethodCall('register', [new Reference($id)]);
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

        $container->addAliases([ToggleRouterInterface::class => new Alias(ToggleRouter::class, true)]);
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

    private function defineDoctrineEventListener(ContainerBuilder $container): void
    {
        if (class_exists(ToolEvents::class)) {
            $container
                ->register(SchemaConfigurationListener::class, SchemaConfigurationListener::class)
                ->addArgument(new Reference(OnOffStrategyConfigurationRepository::class))
                ->addArgument(new Reference(WhitelistStrategyConfigurationRepository::class))
                ->addArgument(new Reference(PercentageStrategyConfigurationRepository::class))
                ->addTag('doctrine.event_listener', ['event' => ToolEvents::postGenerateSchema])
            ;
        }
    }
}
