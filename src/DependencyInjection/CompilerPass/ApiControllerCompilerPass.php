<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;

class ApiControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $controllers = $container->findTaggedServiceIds('valantic.pimcore_api_doc.controller');

        $docsGeneratorService = $container->findDefinition(DocsGeneratorInterface::class);
        $docsGeneratorService->setArgument('$controllers', array_keys($controllers));
    }
}
