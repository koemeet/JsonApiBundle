<?php

namespace Mango\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mango_json_api');

        $rootNode
            ->children()
                ->booleanNode('show_version_info')->defaultValue(true)->end()
                ->integerNode('include_max_depth')->defaultNull(true)->end()
                ->arrayNode('resources')
                    ->prototype('scalar')
                        ->validate()
                        ->ifTrue(function($value) { return false === class_exists($value); })
                            ->thenInvalid('Invalid resource class name %s')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
