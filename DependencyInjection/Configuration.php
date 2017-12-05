<?php

namespace HBM\HelperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
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
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('hbm_helper');

    $rootNode
      ->children()
        ->arrayNode('bitly')
          ->prototype('array')
            ->children()
              ->scalarNode('client_id')->end()
              ->scalarNode('client_secret')->end()
              ->scalarNode('user_login')->end()
              ->scalarNode('user_password')->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('blitline')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('appid')->defaultValue('')->end()
            ->arrayNode('postback')->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('url')->defaultValue('')->info('Base url to use for postback.')->end()
                ->scalarNode('route')->defaultValue('')->info('Route name to use for postback.')->end()
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('webshrinker')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('access_key')->defaultValue('')->end()
            ->scalarNode('secret_key')->defaultValue('')->end()
          ->end()
        ->end()
        ->arrayNode('cleverreach')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('apikey')->defaultValue('')->end()
            ->scalarNode('listid')->info('Subscribe/unsubscribe from this list.')->defaultValue('')->end()
            ->scalarNode('formid')->info('Send activation email of this form.')->defaultValue('')->end()
            ->scalarNode('source')->defaultValue('')->end()
            ->arrayNode('doi')->addDefaultsIfNotSet()
              ->children()
                ->scalarNode('info')->defaultValue('')->end()
                ->scalarNode('data')->defaultValue('')->info('Should contain field keys seperated by ":".')->end()
              ->end()
            ->end()
            ->arrayNode('fields')->defaultValue(array())
              ->prototype('array')
                ->children()
                  ->scalarNode('key')->info('The name of the field in CleverReach.')->end()
                  ->scalarNode('value')->info('The name of the function of the entity.')->end()
                ->end()
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('hmac')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('secret')->defaultValue('')->end()
          ->end()
        ->end()
        ->arrayNode('s3')
          ->prototype('array')
            ->children()
              ->scalarNode('key')->defaultValue('')->end()
              ->scalarNode('secret')->defaultValue('')->end()
              ->scalarNode('bucket')->defaultValue('')->end()
              ->scalarNode('region')->defaultValue('eu-central-1')->end()
              ->scalarNode('local')->defaultValue('./')->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('sanitizing')->addDefaultsIfNotSet()
          ->children()
            ->scalarNode('sep')->defaultValue('/')->end()
            ->scalarNode('language')->defaultValue('de')->end()
          ->end()
        ->end()
      ->end()
    ->end();

    return $treeBuilder;
  }

}
