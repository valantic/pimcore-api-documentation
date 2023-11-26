<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\DataTypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\SimplePropertyDoc;

/**
 * @implements DataTypeParserInterface<SimplePropertyDoc>
 */
class FloatParser implements DataTypeParserInterface
{
    public function parse(\ReflectionProperty $reflectionProperty): SimplePropertyDoc
    {
        $propertyDoc = new SimplePropertyDoc();

        $propertyDoc
            ->setName($reflectionProperty->getName())
            ->setType(DataTypeEnum::FLOAT->value)
            ->setNullable($reflectionProperty->getType()?->allowsNull() ?? true);

        return $propertyDoc;
    }

    public function supports(\ReflectionProperty $reflectionProperty): bool
    {
        return
            $reflectionProperty->getType() instanceof \ReflectionNamedType
            && $reflectionProperty->getType()->getName() === 'float';
    }
}
