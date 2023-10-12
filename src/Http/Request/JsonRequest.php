<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

use Symfony\Component\HttpFoundation\Request;

abstract class JsonRequest extends ApiRequest
{
    protected function hydrate(Request $request): void
    {
        $requestContent = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $reflectionObject = new \ReflectionObject($this);
        $publicProperties = $reflectionObject->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($publicProperties as $publicProperty) {
            $propertyName = $publicProperty->getName();
            $typeHint = '';
            $propertyType = $publicProperty->getType();

            if ($propertyType instanceof \ReflectionNamedType) {
                $typeHint = $propertyType->getName();
            }

            $defaultValue = $this->$propertyName ?? null;
            $propertyValue = $requestContent[$propertyName] ?? $defaultValue;

            if (in_array($typeHint, ['float', 'int', 'bool'], true)) {
                settype($propertyValue, $typeHint);
            }

            $this->$propertyName = $propertyValue;
        }
    }
}
