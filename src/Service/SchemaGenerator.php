<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Valantic\PimcoreApiDocumentationBundle\Contract\Model\Component\Property\ComponentSchemaPropertyInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ComponentPropertyDocGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

readonly class SchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private ComponentPropertyDocGeneratorInterface $componentPropertyDocGenerator,
        private DtoDecorator $dtoDecorator,
        private RequestDecorator $requestDecorator,
    ) {
    }

    public function generateForDto(string $dtoClass): array
    {
        $dtoReflection = new \ReflectionClass($dtoClass);
        $schemaName = $this->dtoDecorator->getDocsDescription($dtoClass);

        $propertiesData = [];

        $componentSchemas = [];

        foreach ($dtoReflection->getProperties() as $property) {
            $propertyDoc = $this->componentPropertyDocGenerator->generate($property);

            if ($propertyDoc instanceof ComponentSchemaPropertyInterface) {
                foreach ($propertyDoc->getSchemas() as $propertySchema) {
                    $componentSchemas[$propertySchema->getName()] = $propertySchema;
                }
            }

            $propertiesData[] = $propertyDoc;
        }

        $componentSchema = new ComponentSchemaDoc();

        $componentSchema
            ->setName($schemaName)
            ->setType(ComponentSchemaDoc::TYPE_OBJECT)
            ->setProperties($propertiesData)
        ;

        $componentSchemas[$schemaName] = $componentSchema;

        return $componentSchemas;
    }

    public function generateForRequest(string $requestClass): ComponentSchemaDoc
    {
        $requestReflection = new \ReflectionClass($requestClass);
        $propertiesData = [];

        $requestParameters = $requestReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($requestParameters as $requestParameter) {
            $propertiesData[] = $this->componentPropertyDocGenerator->generate($requestParameter);
        }

        $componentSchema = new ComponentSchemaDoc();
        $componentSchema
            ->setName($this->requestDecorator->getDocsDescription($requestClass))
            ->setType(ComponentSchemaDoc::TYPE_OBJECT)
            ->setProperties($propertiesData)
        ;

        return $componentSchema;
    }

    public function formatComponentSchemaPath(string $schemaName): string
    {
        return sprintf('#/components/schemas/%s', $schemaName);
    }
}
