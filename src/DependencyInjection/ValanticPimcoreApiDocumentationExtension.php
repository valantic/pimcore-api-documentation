<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Valantic\PimcoreApiDocumentationBundle\Http\Controller\ApiControllerInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ValanticPimcoreApiDocumentationExtension extends Extension
{
    public const TAG_CONTROLLERS = 'valantic.pimcore_api_doc.controller';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ApiControllerInterface::class)->addTag(self::TAG_CONTROLLERS);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->setParameter('valantic.pimcore_api_doc.base_docs_path', $config['base_docs_path'] ?? '');
        $container->setParameter('valantic.pimcore_api_doc.docs_route', $config['docs_route'] ?? '');
    }
}
