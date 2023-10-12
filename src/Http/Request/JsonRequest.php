<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

abstract class JsonRequest extends ApiRequest
{
    protected function hydrate(Request $request): void
    {
        $requestContent = json_decode($request->getContent(), true);

        $reflectionObject = new \ReflectionObject($this);
        $publicProperties = $reflectionObject->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($publicProperties as $publicProperty) {
            $propertyName = $publicProperty->getName();

            $defaultValue = $this->$propertyName ?? null;

            $this->$propertyName = $requestContent[$propertyName] ?? $defaultValue;
        }
    }
}
