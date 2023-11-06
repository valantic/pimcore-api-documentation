<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use League\Csv\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\ControllerDoc;

readonly class DocsGenerator implements DocsGeneratorInterface
{
    /**
     * @param array<class-string> $controllers
     */
    public function __construct(
        private array $controllers,
        private ControllerMethodParserInterface $controllerMethodParser,
        private readonly ParameterBagInterface $parameterBag,
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

        $baseDocsPath = $this->parameterBag->get('valantic.pimcore_api_doc.base_docs_path');

        if (!is_string($baseDocsPath)) {
            throw new \Exception('Missing base docs path.');
        }

        $apiDocs = Yaml::parse(file_get_contents($baseDocsPath) ?: '');

        $apiDocs['paths'] = array_merge($apiDocs['paths'] ?? [], $paths);
        $apiDocs['components']['schemas'] = array_merge($schemas, $apiDocs['components']['schemas'] ?? []);

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
