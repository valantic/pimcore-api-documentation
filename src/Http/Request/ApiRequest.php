<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ApiRequest
{
    public function __construct(
        RequestStack $requestStack,
        private readonly ValidatorInterface $validator,
    ) {
        $request = $requestStack->getMainRequest();

        if ($request instanceof Request) {
            $this->hydrate($request);
        }
    }

    public function validate(): ConstraintViolationListInterface
    {
        return $this->validator->validate($this, groups: $this->groups());
    }

    /**
     * @return string[]
     */
    protected function groups(): array
    {
        return ['Default'];
    }

    protected function hydrate(Request $request): void
    {
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
            $propertyValue = $request->get($propertyName, $defaultValue);

            if (in_array($typeHint, ['float', 'int', 'bool'], true)) {
                settype($propertyValue, $typeHint);
            }

            $this->$propertyName = $propertyValue;
        }
    }
}
