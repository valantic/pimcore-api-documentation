<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('valantic_pimcore_api_documentation');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('open_api_version')->end()
            ->arrayNode('info')
            ->children()
            ->scalarNode('title')->end()
            ->scalarNode('description')->end()
            ->scalarNode('version')->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
