<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequest;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\JsonRequest;
use Valantic\PimcoreApiDocumentationBundle\Http\Response\ApiResponse;
use Valantic\PimcoreApiDocumentationBundle\Model\BaseDto;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\ArrayPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\EnumPropertyDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\MethodDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request\ParameterDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request\RequestDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\ResponseDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\RouteDoc;

/**
 * @template T of AbstractPropertyDoc
 */
readonly class ControllerMethodParser implements ControllerMethodParserInterface
{
    /**
     * @param ServiceLocator<DataTypeParserInterface<T>> $dataTypeParsers
     */
    public function __construct(
        private SchemaGeneratorInterface $schemaGenerator,
        private RouterInterface $router,
        private ServiceLocator $dataTypeParsers,
    ) {}

    public function parseMethod(\ReflectionMethod $method): MethodDoc
    {
        $routeDoc = $this->parseRoute($method);
        $requestDoc = $this->parseRequest($method);
        $responseDoc = $this->parseResponses($method);

        $methodDoc = new MethodDoc();
        $methodDoc
            ->setName($method->getName())
            ->setResponsesDoc($responseDoc)
            ->setRouteDoc($routeDoc);

        if ($requestDoc instanceof RequestDoc) {
            $methodDoc->setRequestDoc($requestDoc);
        }

        return $methodDoc;
    }

    private function parseRoute(\ReflectionMethod $method): RouteDoc
    {
        $attributes = $method->getAttributes();

        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Route::class) {
                $routeAttributeArguments = $attribute->getArguments();

                break;
            }
        }

        if (!isset($routeAttributeArguments) || !isset($routeAttributeArguments['path']) || !isset($routeAttributeArguments['methods'])) {
            throw new \Exception('Route not defined.');
        }

        $route = $this->router->getRouteCollection()->get($routeAttributeArguments['name']);

        if ($route === null) {
            throw new \Exception('Route not found.');
        }

        $path = $route->getPath();
        preg_match_all('/{([^}]+)}/', (string) $path, $routeParameters);

        $parsedParameters = [];

        if ($routeParameters !== []) {
            foreach ($routeParameters[1] as $routeParameter) {
                $parameterDoc = new ParameterDoc();

                $parameterDoc
                    ->setName($routeParameter)
                    ->setIn(ParameterDoc::IN_PATH)
                    ->setRequired(true)
                    ->setSchema([
                        'type' => 'string',
                    ]);

                $parsedParameters[] = $parameterDoc;
            }
        }

        $routeDoc = new RouteDoc();

        $routeDoc
            ->setPath($path)
            ->setMethod(strtolower((string) $routeAttributeArguments['methods']))
            ->setParameters($parsedParameters);

        return $routeDoc;
    }

    private function parseRequest(\ReflectionMethod $method): ?RequestDoc
    {
        $parsedParameters = [];
        $requestParameter = null;

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getName() === 'request') {
                $requestParameter = $parameter;

                break;
            }
        }

        if ($requestParameter === null) {
            return null;
        }

        $requestClassType = $requestParameter->getType();

        if (
            !$requestClassType instanceof \ReflectionNamedType
            || empty($requestParameter->getAttributes())
        ) {
            return null;
        }

        /** @var class-string $requestClass */
        $requestClass = $requestClassType->getName();

        if (!is_subclass_of($requestClass, ApiRequest::class)) {
            return null;
        }

        $requestDoc = new RequestDoc();

        foreach ($requestParameter->getAttributes() as $attribute) {
            if ($attribute->getName() === MapQueryString::class) {
                $requestReflection = new \ReflectionClass($requestClass);

                $requestProperties = $requestReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

                foreach ($requestProperties as $property) {
                    $parameterDoc = new ParameterDoc();

                    $propertyDoc = $this->getDataTypeParser($property)->parse($property);

                    $parameterDoc
                        ->setName($property->getName())
                        ->setIn(ParameterDoc::IN_QUERY)
                        ->setRequired(false)
                        ->setSchema($propertyDoc->getSchema());

                    $parsedParameters[] = $parameterDoc;
                }

                continue;
            }

            if ($attribute->getName() === MapRequestPayload::class) {
                if (is_subclass_of($requestClass, JsonRequest::class)) {
                    $requestDoc->setComponentSchemaDoc($this->schemaGenerator->generateForRequest($requestClass));
                    $requestDoc->setRequestBody([
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => $this->schemaGenerator->formatComponentSchemaPath($requestClass::docsDescription()),
                                ],
                            ],
                        ],
                    ]);
                }
            }
        }

        $requestDoc->setParameters($parsedParameters);

        return $requestDoc;
    }

    /**
     * @return ResponseDoc[]
     */
    private function parseResponses(\ReflectionMethod $method): array
    {
        $methodReturnType = $method->getReturnType();

        if ($methodReturnType === null) {
            throw new \Exception(sprintf('Missing return type for method %s::%s', $method->getDeclaringClass()->getName(), $method->getName()));
        }

        $returnTypes = [];

        if ($methodReturnType instanceof \ReflectionUnionType) {
            $returnTypes = $methodReturnType->getTypes();
        } else {
            $returnTypes[] = $methodReturnType;
        }

        $methodResponses = [];

        foreach ($returnTypes as $returnType) {
            if (!method_exists($returnType, 'getName')) {
                continue;
            }

            $responseClassName = $returnType->getName();

            if (!is_subclass_of($responseClassName, ApiResponse::class)) {
                continue;
            }

            $responseDoc = new ResponseDoc();

            $dtoClass = $responseClassName::getDtoClass();

            $responseDoc
                ->setStatus($responseClassName::status())
                ->setDescription($responseClassName::docsDescription());

            if ($dtoClass !== false && is_subclass_of($dtoClass, BaseDto::class)) {
                $schemaName = $dtoClass::docsSchemaName();

                $responseDoc->setComponentSchemas($this->schemaGenerator->generateForDto($dtoClass));
                $responseDoc->setContent([
                    'application/json' => [
                        'schema' => [
                            '$ref' =>  $this->schemaGenerator->formatComponentSchemaPath($schemaName),
                        ],
                    ],
                ]);
            }

            $methodResponses[] = $responseDoc;
        }

        return $methodResponses;
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

        throw new \Exception(sprintf('Property of type %s not supported. Add service that implements %s.', $reflectionProperty->getType(), DataTypeParserInterface::class));
    }
}
