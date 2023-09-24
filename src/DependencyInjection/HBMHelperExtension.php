<?php

namespace HBM\HelperBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HBMHelperExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $configToUse = $config;

        $container->setParameter('hbm.helper.blitline', $configToUse['blitline']);
        $container->setParameter('hbm.helper.screenshotapi', $configToUse['screenshotapi']);
        $container->setParameter('hbm.helper.screenshotlayer', $configToUse['screenshotlayer']);
        $container->setParameter('hbm.helper.webshrinker', $configToUse['webshrinker']);

        $container->setParameter('hbm.helper.bitly', $configToUse['bitly']);
        $container->setParameter('hbm.helper.hmac', $configToUse['hmac']);
        $container->setParameter('hbm.helper.s3', $configToUse['s3']);
        $container->setParameter('hbm.helper.sanitizing', $configToUse['sanitizing']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }
}
