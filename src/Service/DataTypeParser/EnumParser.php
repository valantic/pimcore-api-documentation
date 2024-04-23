<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service\DataTypeParser;

use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\DataTypeEnum;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\EnumPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\SimplePropertyDoc;

/**
 * @implements DataTypeParserInterface<SimplePropertyDoc>
 */
class EnumParser implements DataTypeParserInterface
{
    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        $propertyDoc = new EnumPropertyDoc();

        $propertyTypeName = $reflectionProperty->getType()->getName();

        if (!is_subclass_of($propertyTypeName, \UnitEnum::class)) {
            return $propertyDoc;
        }

        $enumOptions = [];

        foreach ($propertyTypeName::cases() as $enumCase) {
            if ($enumCase instanceof \BackedEnum) {
                $enumOptions[] = $enumCase->value;
            } else {
                $enumOptions[] = $enumCase->name;
            }
        }

        $propertyDoc
            ->setName($reflectionProperty->getName())
            ->setType(DataTypeEnum::STRING->value)
            ->setEnumOptions($enumOptions)
            ->setNullable($reflectionProperty->getType()?->allowsNull() ?? true);

        return $propertyDoc;
    }

    public function supports(\ReflectionProperty $reflectionProperty): bool
    {
        return
            $reflectionProperty->getType() instanceof \ReflectionNamedType
            && is_subclass_of($reflectionProperty->getType()->getName(), \UnitEnum::class);
    }
}
