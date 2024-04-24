<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnsupportedPropertyTypeException;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\DtoPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Service\DtoDecorator;

/**
 * @implements DataTypeParserInterface<DtoPropertyDoc>
 */
readonly class DtoParser implements DataTypeParserInterface
{
    public function __construct(
        private SchemaGeneratorInterface $schemaGenerator,
        private DtoDecorator $dtoDecorator,
    ) {}

    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        if (!$reflectionProperty->getType() instanceof \ReflectionNamedType) {
            throw new UnsupportedPropertyTypeException(sprintf('Unsupported type %s.', $reflectionProperty->getName()));
        }

        /** @var class-string $dtoClass */
        $dtoClass = $reflectionProperty->getType()->getName();

        $schemas = $this->schemaGenerator->generateForDto($dtoClass);

        $propertyDoc = new DtoPropertyDoc();

        $ref = $this->schemaGenerator->formatComponentSchemaPath($this->dtoDecorator->getDocsDescription($dtoClass));

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
            && !$reflectionProperty->getType()->isBuiltin();
    }
}
