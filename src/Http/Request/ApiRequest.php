<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

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

            $snakeCasePropertyName = Str::snake($propertyName);

            $defaultValue = $this->$propertyName ?? null;

            if (
                $request->attributes->has($propertyName)
                || $request->query->has($propertyName)
                || $request->request->has($propertyName)
            ) {
                $this->$propertyName = $request->get($snakeCasePropertyName, $defaultValue);

                continue;
            }

            $this->$propertyName = $request->get($propertyName, $defaultValue);
        }
    }
}
