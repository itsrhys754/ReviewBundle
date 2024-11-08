<?php

namespace Rhys\ReviewBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('review');
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->children()
                        ->scalarNode('book_class')
                            ->defaultValue('App\Entity\Book')
                        ->end()
                        ->scalarNode('user_class')
                            ->defaultValue('App\Entity\User')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('approval')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_approve')
                            ->defaultFalse()
                        ->end()
                        ->integerNode('min_rating')
                            ->defaultValue(0)
                        ->end()
                        ->integerNode('max_rating')
                            ->defaultValue(10)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}