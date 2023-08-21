<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $this->buildApiConfig($treeBuilder->getRootNode());

        return $treeBuilder;
    }

    private function buildApiConfig(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
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
    }
}
