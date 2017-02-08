<?php

namespace Mango\Bundle\JsonApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MangoJsonApiExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('jms_serializer', array(
            'property_naming' => array(
                'separator' => '-'
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('configuration.xml');
        $loader->load('services.xml');

        // TODO: Make this configurable
        $configDir = '%kernel.cache_dir%/json_api';

        $container->getDefinition('json_api.metadata.cache.file_cache')->replaceArgument(0, $configDir);

        $dir = $container->getParameterBag()->resolveValue($configDir);

        if (!file_exists($dir)) {
            if (!$rs = @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        }

        $container->setParameter('json_api.show_version_info', $config['show_version_info']);
        $container->setParameter('json_api.base_uri', $config['base_uri']);
    }
}
