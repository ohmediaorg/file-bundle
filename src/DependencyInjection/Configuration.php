<?php

namespace JstnThms\FileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('jstn_thms_file');
        
        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('upload_dir')
                    ->defaultValue('')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
