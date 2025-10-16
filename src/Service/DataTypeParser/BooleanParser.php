<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\DataTypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\SimplePropertyDoc;

/**
 * @implements DataTypeParserInterface<SimplePropertyDoc>
 */
class BooleanParser implements DataTypeParserInterface
{
    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        $propertyDoc = new SimplePropertyDoc();

        $propertyDoc
            ->setName($reflectionProperty->getName())
            ->setDocBlock($reflectionProperty->getDocComment())
            ->setType(DataTypeEnum::BOOLEAN->value)
            ->setNullable($reflectionProperty->getType()?->allowsNull() ?? true)
        ;

        return $propertyDoc;
    }

    public function supports(\ReflectionProperty $reflectionProperty): bool
    {
        return
            $reflectionProperty->getType() instanceof \ReflectionNamedType
            && $reflectionProperty->getType()->getName() === 'bool';
    }
}
