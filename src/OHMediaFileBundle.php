<?php

namespace OHMedia\FileBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class OHMediaFileBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
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
                    ->integerNode('max_image_dimension')
                        ->min(100)
                        ->defaultValue(1200)
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $containerConfigurator,
        ContainerBuilder $containerBuilder
    ): void {
        $containerConfigurator->import('../config/services.yaml');

        $containerConfigurator->parameters()
            ->set('oh_media_file.file_browser.enabled', $config['file_browser']['enabled'])
            ->set('oh_media_file.file_browser.limit_mb', $config['file_browser']['limit_mb'])
            ->set('oh_media_file.file_browser.max_image_dimension', $config['file_browser']['max_image_dimension'])
        ;

        $this->registerWidget($containerBuilder);
    }

    protected function registerWidget(ContainerBuilder $containerBuilder)
    {
        $resource = '@OHMediaFile/Form/file_entity_widget.html.twig';

        $containerBuilder->setParameter('twig.form.resources', array_merge(
            $containerBuilder->getParameter('twig.form.resources'),
            [$resource]
        ));
    }
}
