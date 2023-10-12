<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use League\Csv\Exception;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\ControllerDoc;

readonly class DocsGenerator implements DocsGeneratorInterface
{
    /**
     * @param array<class-string> $controllers
     * @param mixed[] $apiConfig
     */
    public function __construct(
        private array $controllers,
        private array $apiConfig,
        private ControllerMethodParserInterface $controllerMethodParser,
    ) {
    }

    public function generate(string $docsPath): void
    {
        $controllerDocs = [];

        foreach ($this->controllers as $controller) {
            $controllerDocs[] = $this->generateControllerDoc($controller);
        }

        $paths = [];
        $schemas = [];

        foreach ($controllerDocs as $controllerDoc) {
            foreach ($controllerDoc->getMethodsDocs() as $methodDoc) {
                $paths[$methodDoc->getRouteDoc()->getPath()][$methodDoc->getRouteDoc()->getMethod()] = $methodDoc;
                $schemas = array_merge($schemas, $methodDoc->getComponentSchemas());
            }
        }

        $apiDocs = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $this->apiConfig['title'],
                'version' => $this->apiConfig['version'],
                'description' => $this->apiConfig['description'],
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => $schemas,
            ],
        ];

        $this->saveFile($docsPath, $apiDocs);
    }

    /**
     * @param class-string $controllerClass
     *
     * @throws \ReflectionException
     */
    private function generateControllerDoc(string $controllerClass): ControllerDoc
    {
        $methods = $this->getValidControllerMethods($controllerClass);

        $controllerDoc = new ControllerDoc();

        foreach ($methods as $method) {
            $methodDoc = $this->controllerMethodParser->parseMethod($method);
            $controllerDoc->addMethodDoc($methodDoc);
        }

        return $controllerDoc;
    }

    /**
     * @param class-string $controllerClass
     *
     * @throws \ReflectionException
     *
     * @return \ReflectionMethod[]
     */
    private function getValidControllerMethods(string $controllerClass): array
    {
        $controllerReflection = new \ReflectionClass($controllerClass);
        $methods = $controllerReflection->getMethods();

        return array_filter($methods, function($method) use ($controllerClass) {
            $methodClass = $method->getDeclaringClass()->getName();

            return $method->getName() !== '__construct' && $methodClass === $controllerClass;
        });
    }

    /**
     * @param mixed[] $content
     *
     * @throws Exception
     */
    private function saveFile(string $filePath, array $content): void
    {
        $file = fopen($filePath, 'w');

        $jsonContent = json_encode($content, JSON_THROW_ON_ERROR);

        if ($file !== false && $jsonContent !== false) {
            fwrite($file, $jsonContent);
            fclose($file);

            return;
        }

        throw new Exception('Failed to create file.');
    }
}
