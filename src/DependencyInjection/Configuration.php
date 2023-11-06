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
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('base_docs_path')
            ->cannotBeEmpty()
            ->defaultValue(sprintf('%s/config/api-docs/docs.yaml', PIMCORE_PROJECT_ROOT))
            ->end()
            ->end();

        return $treeBuilder;
    }
}
