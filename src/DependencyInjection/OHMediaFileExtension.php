<?php

namespace OHMedia\FileBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OHMediaFileExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('oh_media_file.file_browser.enabled', $config['file_browser']['enabled']);
        $container->setParameter('oh_media_file.file_browser.limit_gb', $config['file_browser']['limit_gb']);

        $this->registerWidget($container);
    }

    protected function registerWidget(ContainerBuilder $container)
    {
        $resource = '@OHMediaFile/Form/file_entity_widget.html.twig';

        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            [$resource]
        ));
    }
}
