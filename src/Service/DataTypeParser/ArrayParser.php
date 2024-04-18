<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\DataTypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\ArrayPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Service\DtoDecorator;

/**
 * @implements DataTypeParserInterface<ArrayPropertyDoc>
 */
readonly class ArrayParser implements DataTypeParserInterface
{
    public function __construct(
        private DocBlockParserInterface $docBlockParser,
        private SchemaGeneratorInterface $schemaGenerator,
        private DtoDecorator $dtoDecorator,
    ) {}

    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        $typeHints = $this->determineTypeHints($reflectionProperty);

        $arrayItems = [];

        foreach ($typeHints as $typeHint) {
            if (str_contains($typeHint, '\\')) {
                /** @var class-string $typeHint */
                $arrayDtoSchemaName = $this->dtoDecorator->getDocsDescription($typeHint);
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

        $propertyDoc = new ArrayPropertyDoc();

        if (count($arrayItems) > 1) {
            $propertyDoc->setItems(['oneOf' => $arrayItems]);
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

    /**
     * @return string[]
     */
    private function determineTypeHints(\ReflectionProperty $reflectionProperty): array
    {
        $propertyName = $reflectionProperty->getName();
        $declaringClassReflection = new \ReflectionClass($reflectionProperty->getDeclaringClass()->getName());

        $docBlocksTypeHints = [];
        $docBlock = null;

        if ($reflectionProperty->getDocComment()) {
            $docBlock = $reflectionProperty->getDocComment();
        } elseif (
            $declaringClassReflection->hasMethod('__construct')
            && $declaringClassReflection->getMethod('__construct')->getDocComment() !== false
        ) {
            $docBlock = $declaringClassReflection->getMethod('__construct')->getDocComment();
        }

        if ($docBlock !== null) {
            $docBlocksTypeHints = $this->docBlockParser->parseDocBlock($docBlock);
        }

        $typeHints = [];

        if (isset($docBlocksTypeHints[$propertyName])) {
            $typeHints = $this->docBlockParser->determineTypeHint($docBlocksTypeHints[$propertyName], $declaringClassReflection);
        }

        return $typeHints;
    }
}
