<?php

namespace Strut\StrutBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('strut');

        $rootNode
            ->children()
                ->arrayNode('languages')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('language')
                    ->defaultValue('fr')
                ->end()
                ->scalarNode('version')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
