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
                    ->integerNode('limit_mb')
                        ->min(100)
                        ->max(5120)
                        ->defaultValue(1024)
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
