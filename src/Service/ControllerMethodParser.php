<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\SchemaGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequest;
use Valantic\PimcoreApiDocumentationBundle\Http\Request\JsonRequest;
use Valantic\PimcoreApiDocumentationBundle\Http\Response\ApiResponse;
use Valantic\PimcoreApiDocumentationBundle\Model\BaseDto;
use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\MethodDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request\ParameterDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\Request\RequestDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\ResponseDoc;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\RouteDoc;

readonly class ControllerMethodParser implements ControllerMethodParserInterface
{
    public function __construct(
        private SchemaGeneratorInterface $schemaGenerator,
        private RouterInterface $router,
    ) {
    }

    public function parseMethod(\ReflectionMethod $method): MethodDoc
    {
        $routeDoc = $this->parseRoute($method);
        $requestData = $this->parseRequest($method);
        $responsesData = $this->parseResponses($method);

        $methodDoc = new MethodDoc();
        $methodDoc
            ->setName($method->getName())
            ->setResponsesDoc($responsesData['responses'])
            ->setComponentSchemas($responsesData['schemas'])
            ->setRouteDoc($routeDoc);

        if ($requestData !== null) {
            $methodDoc->setRequestDoc($requestData['request']);
            $methodDoc->setComponentSchemas(array_merge($methodDoc->getComponentSchemas(), $requestData['schemas']));
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

    /**
     * @return array{request: RequestDoc, schemas: array<ComponentSchemaDoc>}|null
     */
    private function parseRequest(\ReflectionMethod $method): ?array
    {
        $parsedParameters = [];
        $schemas = [];

        $requestClassType = null;

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getName() === 'request') {
                $requestClassType = $parameter->getType();

                break;
            }
        }

        if (!$requestClassType instanceof \ReflectionNamedType) {
            return null;
        }

        /** @var class-string $requestClass */
        $requestClass = $requestClassType->getName();

        if (!is_subclass_of($requestClass, ApiRequest::class)) {
            return null;
        }

        $requestDoc = new RequestDoc();

        // @TODO refactor
        if (is_subclass_of($requestClass, JsonRequest::class)) {
            $schemas = $this->schemaGenerator->generateForRequest($requestClass);

            $requestDoc->setRequestBody([
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => $this->schemaGenerator->formatComponentSchemaPath(array_keys($schemas)[0]),
                        ],
                    ],
                ],
            ]);
        } else {
            $requestReflection = new \ReflectionClass($requestClass);

            $requestParameters = $requestReflection->getProperties(\ReflectionProperty::IS_PUBLIC);

            foreach ($requestParameters as $requestParameter) {
                $parameterDoc = new ParameterDoc();

                $parameterDoc
                    ->setName($requestParameter->getName())
                    ->setIn(ParameterDoc::IN_QUERY)
                    ->setRequired(false)
                    ->setSchema([
                        'type' => 'string',
                    ]);

                $parsedParameters[] = $parameterDoc;
            }

            $requestDoc->setParameters($parsedParameters);
        }

        return [
            'request' => $requestDoc,
            'schemas' => $schemas,
        ];
    }

    /**
     * @return array{responses: array<ResponseDoc>, schemas: array<ComponentSchemaDoc>}
     */
    private function parseResponses(\ReflectionMethod $method): array
    {
        $methodReturnType = $method->getReturnType();

        if ($methodReturnType === null) {
            throw new \Exception(sprintf(
                'Missing return type for method %s::%s',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            ));
        }

        $returnTypes = [];

        if ($methodReturnType instanceof \ReflectionUnionType) {
            $returnTypes = $methodReturnType->getTypes();
        } else {
            $returnTypes[] = $methodReturnType;
        }

        $methodResponses = [];
        $componentSchemas = [];

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
                $componentSchemas = $this->schemaGenerator->generateForDto($dtoClass);

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

        return [
            'schemas' => $componentSchemas,
            'responses' => $methodResponses,
        ];
    }
}
