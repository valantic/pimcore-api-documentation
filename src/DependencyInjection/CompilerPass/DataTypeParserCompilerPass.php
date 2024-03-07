<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ComponentPropertyDocGeneratorInterface;

class DataTypeParserCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $dataTypeParsers = $container->findTaggedServiceIds('valantic.pimcore_api_doc.data_type_parser');

        $dataTypeParsersServices = [];

        foreach (array_keys($dataTypeParsers) as $dataTypeParser) {
            $dataTypeParsersServices[$dataTypeParser] = new Reference($dataTypeParser);
        }

        $docsGeneratorService = $container->findDefinition(ComponentPropertyDocGeneratorInterface::class);
        $docsGeneratorService->setArgument(
            '$dataTypeParsers',
            ServiceLocatorTagPass::register($container, $dataTypeParsersServices),
        );
    }
}
