<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Routing\RouterInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DataTypeParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Exception\IncompleteRouteException;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnsupportedPropertyTypeException;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnsupportedRouteException;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequestInterface;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\Contracts\HasJsonPayload;
use Valantic\PimcoreApiDocumentationBundle\Http\Response\ApiResponseInterface;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;
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
        private DtoDecorator $dtoDecorator,
        private RequestDecorator $requestDecorator,
        private ResponseDecorator $responseDecorator,
        private ServiceLocator $dataTypeParsers,
        private DocBlockParserInterface $docBlockParser,
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
            if ($attribute->getName() === RouteAnnotation::class || $attribute->getName() === RouteAttribute::class) {
                $routeAttributeArguments = $attribute->getArguments();

                break;
            }
        }

        if (!isset($routeAttributeArguments['path'], $routeAttributeArguments['methods'])) {
            throw new IncompleteRouteException(sprintf('Route in %s::%s not defined or missing attributes "path" and/or "methods".', $method->getDeclaringClass()->getName(), $method->getName()));
        }

        if (!isset($routeAttributeArguments['name'])) {
            throw new IncompleteRouteException(sprintf('Route in %s::%s does not have a "name" property.', $method->getDeclaringClass()->getName(), $method->getName()));
        }

        $route = $this->router->getRouteCollection()->get($routeAttributeArguments['name']);

        if ($route === null) {
            throw new IncompleteRouteException(sprintf('Route %s not found.', $routeAttributeArguments['name']));
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

        $methods = $routeAttributeArguments['methods'];

        if (is_array($methods)) {
            if (count($methods) > 1) {
                throw new UnsupportedRouteException(sprintf('Route %s has multiple methods. This is not yet supported.', $routeAttributeArguments['name']));
            }
            $methods = $methods[0];
        }

        $routeDoc
            ->setPath($path)
            ->setMethod(strtolower((string) $methods))
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

        if (!is_subclass_of($requestClass, ApiRequestInterface::class)) {
            return null;
        }
        $requestDoc = new RequestDoc();

        foreach ($requestParameter->getAttributes() as $attribute) {
            if ($attribute->getName() === MapQueryString::class) {
                // TODO: parse nested
                $requestReflection = new \ReflectionClass($requestClass);

                $requestProperties = $requestReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

                foreach ($requestProperties as $property) {
                    $parameterDoc = new ParameterDoc();

                    $propertyDoc = $this->getDataTypeParser($property)->parse($property);

                    $parameterDoc
                        ->setName($property->getName())
                        ->setDescription($this->getDescription($propertyDoc))
                        ->setIn(ParameterDoc::IN_QUERY)
                        ->setRequired(false)
                        ->setSchema($propertyDoc->getSchema());

                    $parsedParameters[] = $parameterDoc;
                }

                continue;
            }

            if (($attribute->getName() === MapRequestPayload::class) && is_subclass_of($requestClass, HasJsonPayload::class)) {
                $requestDoc->setComponentSchemaDoc($this->schemaGenerator->generateForRequest($requestClass));
                $requestDoc->setRequestBody([
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => $this->schemaGenerator->formatComponentSchemaPath(
                                    $this->requestDecorator->getDocsDescription($requestClass)
                                ),
                            ],
                        ],
                    ],
                ]);
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
            throw new IncompleteRouteException(sprintf('Missing return type for method %s::%s', $method->getDeclaringClass()->getName(), $method->getName()));
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

            /** @var class-string $responseClassName */
            $responseClassName = $returnType->getName();

            if (!is_a($responseClassName, ApiResponseInterface::class, true)) {
                continue;
            }

            $responseDoc = new ResponseDoc();

            $dtoClass = $responseClassName::getDtoClass();

            $responseDoc
                ->setStatus($responseClassName::status())
                ->setDescription($this->responseDecorator->getDocsDescription($responseClassName));

            if ($dtoClass !== false) {
                $schemaName = $this->dtoDecorator->getDocsDescription($dtoClass);

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

        throw new UnsupportedPropertyTypeException(sprintf('Property of type %s not supported. Add service that implements %s.', $reflectionProperty->getType(), DataTypeParserInterface::class));
    }

    private function getDescription(AbstractPropertyDoc $propertyDoc): ?string
    {
        $docBlock = $propertyDoc->getDocBlock();

        if ($docBlock === null) {
            return null;
        }

        $nodes = $this->docBlockParser->parseDocBlock($docBlock);

        foreach ($nodes as $node) {
            if (!$node instanceof PhpDocTagNode || !$node->value instanceof VarTagValueNode) {
                continue;
            }

            return $node->value->description;
        }

        return null;
    }
}
