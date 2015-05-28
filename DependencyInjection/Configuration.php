<?php

namespace Zim\AutoincBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zim_autoinc');

         $rootNode
            ->children()
                ->arrayNode('autoincrement')
                    ->children()
                        ->scalarNode('database_name')->cannotBeEmpty()
                            ->end()
                        ->scalarNode('collection')->defaultValue('counters')
                            ->end()
                        ->arrayNode('counters')
                               ->isRequired()
                               ->requiresAtLeastOneElement()
                               ->prototype('scalar')
                               ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
