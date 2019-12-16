<?php

namespace Trompette\FeatureToggles\Bundle;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class FeatureTogglesConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new UnifiedTreeBuilder('feature_toggles');
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

class UnifiedTreeBuilder extends TreeBuilder
{
    public function __construct(string $name, string $type = 'array', NodeBuilder $builder = null)
    {
        $builder = $builder ?: new NodeBuilder();
        $this->root = $builder->node($name, $type)->setParent($this);
    }

    public function getRootNode(): NodeDefinition
    {
        return $this->root;
    }
}
