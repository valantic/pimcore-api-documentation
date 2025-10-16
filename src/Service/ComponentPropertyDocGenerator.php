<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ComponentPropertyDocGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnsupportedPropertyTypeException;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;

/**
 * @template T of AbstractPropertyDoc
 */
readonly class ComponentPropertyDocGenerator implements ComponentPropertyDocGeneratorInterface
{
    /**
     * @param ServiceLocator<DataTypeParserInterface<T>> $dataTypeParsers
     */
    public function __construct(
        private ServiceLocator $dataTypeParsers,
    ) {
    }

    /**
     * @return T
     */
    public function generate(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc
    {
        $dataTypeParser = $this->getDataTypeParser($reflectionProperty);

        return $dataTypeParser->parse($reflectionProperty);
    }

    /**
     * @return DataTypeParserInterface<T>
     */
    private function getDataTypeParser(\ReflectionProperty $reflectionProperty): DataTypeParserInterface
    {
        foreach (array_keys($this->dataTypeParsers->getProvidedServices()) as $key) {
            /** @var DataTypeParserInterface<T> $dataTypeParser */
            $dataTypeParser = $this->dataTypeParsers->get($key);

            if ($dataTypeParser->supports($reflectionProperty)) {
                return $dataTypeParser;
            }
        }

        throw new UnsupportedPropertyTypeException(sprintf('Property of type %s in %s::$%s not supported.', $reflectionProperty->getType(), $reflectionProperty->getDeclaringClass()->getName(), $reflectionProperty->getName()));
    }
}
