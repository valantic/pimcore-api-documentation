<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Model\BaseDto;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\DtoPropertyDoc;

/**
 * @implements DataTypeParserInterface<DtoPropertyDoc>
 */
readonly class DtoParser implements DataTypeParserInterface
{
    public function __construct(
        private SchemaGeneratorInterface $schemaGenerator,
    ) {
    }

    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
            throw new \Exception('Unsupported type.');
        }

        $dtoClass = $reflectionProperty->getType()->getName();

        if (!is_subclass_of($dtoClass, BaseDto::class)) {
            throw new \Exception('Unsupported type.');
        }

        $schemas = $this->schemaGenerator->generateForDto($dtoClass);

        $propertyDoc = new DtoPropertyDoc();

        $ref = $this->schemaGenerator->formatComponentSchemaPath($dtoClass::docsSchemaName());

        $propertyDoc
            ->setName($reflectionProperty->getName())
            ->setType('object')
            ->setNullable($reflectionProperty->getType()->allowsNull())
            ->setRef($ref)
            ->setSchemas($schemas);

        return $propertyDoc;
    }

    public function supports(\ReflectionProperty $reflectionProperty): bool
    {
        return
            $reflectionProperty->getType() instanceof \ReflectionNamedType
            && is_subclass_of($reflectionProperty->getType()->getName(), BaseDto::class);
    }
}
