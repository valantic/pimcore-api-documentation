<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\DataTypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\BaseDto;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\ArrayPropertyDoc;

/**
 * @implements DataTypeParserInterface<ArrayPropertyDoc>
 */
readonly class ArrayParser implements DataTypeParserInterface
{
    public function __construct(
        private DocBlockParserInterface $docBlockParser,
        private SchemaGeneratorInterface $schemaGenerator,
    ) {}

    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        $propertyName = $reflectionProperty->getName();
        $declaringClassReflection = new \ReflectionClass($reflectionProperty->getDeclaringClass()->getName());

        $docBlocksTypeHints = [];

        if ($declaringClassReflection->getMethod('__construct')->getDocComment() !== false) {
            $docBlocksTypeHints = $this->docBlockParser->parseDocBlock(
                $declaringClassReflection->getMethod('__construct')->getDocComment()
            );
        }

        $typeHints = [];

        if (isset($docBlocksTypeHints[$propertyName])) {
            $typeHints = $this->docBlockParser->determineTypeHint($docBlocksTypeHints[$propertyName], $declaringClassReflection);
        }

        $arrayItems = [];

        if (count($typeHints) !== 0) {
            foreach ($typeHints as $typeHint) {
                if (is_subclass_of($typeHint, BaseDto::class)) {
                    $arrayDtoSchemaName = $typeHint::docsSchemaName();
                    $schemas = $this->schemaGenerator->generateForDto($typeHint);

                    $arrayItems[] = [
                        '$ref' => $this->schemaGenerator->formatComponentSchemaPath($arrayDtoSchemaName),
                    ];
                } else {
                    $arrayItems[] = [
                        'type' => $typeHint,
                    ];
                }
            }
        }

        $propertyDoc = new ArrayPropertyDoc();

        if (count($arrayItems) > 1) {
            $propertyDoc->setItems([
                'oneOf' => $arrayItems,
            ]);
        } else {
            $propertyDoc->setItems($arrayItems[0] ?? []);
        }

        $propertyDoc
            ->setName($reflectionProperty->getName())
            ->setType(DataTypeEnum::ARRAY->value)
            ->setNullable($reflectionProperty->getType()?->allowsNull() ?? true)
            ->setSchemas($schemas ?? []);

        return $propertyDoc;
    }

    public function supports(\ReflectionProperty $reflectionProperty): bool
    {
        return
            $reflectionProperty->getType() instanceof \ReflectionNamedType
            && $reflectionProperty->getType()->getName() === 'array';
    }
}
