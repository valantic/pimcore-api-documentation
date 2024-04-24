<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use League\Csv\Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Symfony\Component\Routing\Attribute\Route as RouteAttribute;
use Symfony\Component\Yaml\Yaml;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\ControllerMethodParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocsGeneratorInterface;
use Valantic\PimcoreApiDocumentationBundle\DependencyInjection\ValanticPimcoreApiDocumentationExtension;
use Valantic\PimcoreApiDocumentationBundle\Exception\UnableToGenerateDocumentation;
use Valantic\PimcoreApiDocumentationBundle\Model\Doc\ControllerDoc;

readonly class DocsGenerator implements DocsGeneratorInterface
{
    /**
     * @param array<class-string> $controllers
     */
    public function __construct(
        #[TaggedIterator(ValanticPimcoreApiDocumentationExtension::TAG_CONTROLLERS)]
        private iterable $controllers,
        private ControllerMethodParserInterface $controllerMethodParser,
        #[Autowire('%valantic.pimcore_api_doc.base_docs_path%')]
        private string $baseDocsPath,
    ) {}

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

                if ($methodDoc->getRequestDoc()?->getComponentSchemaDoc() !== null) {
                    $requestSchema = $methodDoc->getRequestDoc()->getComponentSchemaDoc();
                    $schemas[$requestSchema->getName()] = $requestSchema;
                }

                foreach ($methodDoc->getResponsesDoc() as $responseDoc) {
                    $schemas = array_merge($schemas, $responseDoc->getComponentSchemas());
                }
            }
        }

        $apiDocs = Yaml::parse(file_get_contents($this->baseDocsPath) ?: '');

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

        return array_filter($methods, static function($method) use ($controllerClass): bool {
            $methodClass = $method->getDeclaringClass()->getName();

            return $method->getName() !== '__construct'
                && $methodClass === $controllerClass
                && $method->isPublic()
                && array_reduce(
                    $method->getAttributes(),
                    fn (bool $carry, \ReflectionAttribute $attribute): bool => $carry || $attribute->getName() === RouteAnnotation::class || $attribute->getName() === RouteAttribute::class,
                    false
                );
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

        $jsonContent = json_encode($content, \JSON_THROW_ON_ERROR);

        if ($file !== false && $jsonContent !== false) {
            fwrite($file, $jsonContent);
            fclose($file);

            return;
        }

        throw new UnableToGenerateDocumentation('Failed to create file.');
    }
}
