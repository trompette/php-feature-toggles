<?php

namespace Trompette\FeatureToggles\Bundle;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class FeatureTogglesConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('feature_toggles');
        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('doctrine_dbal_connection')->defaultValue('doctrine.dbal.default_connection')->end()
                ->arrayNode('declared_features')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('description')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('strategy')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                    ->end()
                ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
