<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\TypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\BaseDto;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

readonly class SchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private DocBlockParserInterface $docBlockParser,
    ) {}

    public function generateForDto(string $dtoClass): array
    {
        $componentSchemas = [];

        $dtoReflection = new \ReflectionClass($dtoClass);
        $schemaName = $dtoClass::docsSchemaName();

        $propertiesData = [];

        $docBlocksTypeHints = [];

        if ($dtoReflection->getMethod('__construct')->getDocComment() !== false) {
            $docBlocksTypeHints = $this->docBlockParser->parseDocBlock($dtoReflection->getMethod('__construct')->getDocComment());
        }

        foreach ($dtoReflection->getProperties() as $property) {
            if (!$property->getType() instanceof \ReflectionNamedType) {
                continue;
            }

            $propertyName = $property->getName();
            $propertyType = $property->getType()->getName();

            $propertyDoc = new ComponentPropertyDoc();

            if (TypeEnum::tryFrom($propertyType) !== null) {
                $propertyDoc->setType(TypeEnum::from($propertyType)->swaggerEnum());

                if (TypeEnum::from($propertyType)->value === TypeEnum::ARRAY->value) {
                    if (array_key_exists($propertyName, $docBlocksTypeHints)) {
                        $typeHints = $this->docBlockParser->determineTypeHint($docBlocksTypeHints[$propertyName], $dtoReflection);

                        $arrayItems = [];

                        foreach ($typeHints as $typeHint) {
                            if (is_subclass_of($typeHint, BaseDto::class)) {
                                $arrayDtoSchemaName = $typeHint::docsSchemaName();
                                $componentSchemas = array_merge($componentSchemas, $this->generateForDto($typeHint));

                                $arrayItems[] = [
                                    '$ref' => $this->formatComponentSchemaPath($arrayDtoSchemaName),
                                ];
                            } else {
                                $arrayItems[] = [
                                    'type' => $typeHint,
                                ];
                            }
                        }

                        if (count($arrayItems) > 1) {
                            $propertyDoc->setItems([
                                'oneOf' => $arrayItems,
                            ]);
                        } else {
                            $propertyDoc->setItems($arrayItems[0] ?? []);
                        }
                    }
                }
            } elseif (class_exists($propertyType) && is_subclass_of($propertyType, BaseDto::class)) {
                $componentSchemas = array_merge($componentSchemas, $this->generateForDto($propertyType));
                $propertyDoc->setRef($this->formatComponentSchemaPath($propertyType::docsSchemaName()));
            }

            $propertyDoc->setName($propertyName);

            $propertiesData[] = $propertyDoc;
        }

        $componentSchema = new ComponentSchemaDoc();

        $componentSchema
            ->setName($schemaName)
            ->setType(ComponentSchemaDoc::TYPE_OBJECT)
            ->setProperties($propertiesData);

        $componentSchemas[$schemaName] = $componentSchema;

        return $componentSchemas;
    }

    public function generateForRequest(string $requestClass): array
    {
        $requestReflection = new \ReflectionClass($requestClass);
        $propertiesData = [];

        $requestParameters = $requestReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($requestParameters as $requestParameter) {
            $propertyDoc = new ComponentPropertyDoc();

            if (!$requestParameter->getType() instanceof \ReflectionNamedType) {
                continue;
            }

            $propertyDoc->setName(Str::snake($requestParameter->getName()));
            $propertyDoc->setType(TypeEnum::from($requestParameter->getType()->getName())->swaggerEnum());

            $propertiesData[] = $propertyDoc;
        }

        $componentSchema = new ComponentSchemaDoc();
        $componentSchema
            ->setName($requestReflection->getShortName())
            ->setType(ComponentSchemaDoc::TYPE_OBJECT)
            ->setProperties($propertiesData);

        return [
            $componentSchema->getName() => $componentSchema,
        ];
    }

    public function formatComponentSchemaPath(string $schemaName): string
    {
        return sprintf('#/components/schemas/%s', $schemaName);
    }
}
