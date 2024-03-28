<?php

namespace OHMedia\FileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oh_media_file');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('file_browser')
                  ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()
                    ->floatNode('limit_gb')
                        ->min(1)
                        ->max(10)
                        ->defaultValue(5)
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
