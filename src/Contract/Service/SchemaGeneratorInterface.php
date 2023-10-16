<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\ComponentSchemaDoc;

interface SchemaGeneratorInterface
{
    /**
     * @param class-string $dtoClass
     *
     * @return ComponentSchemaDoc[]
     */
    public function generateForDto(string $dtoClass): array;

    /**
     * @param class-string $requestClass
     *
     * @return ComponentSchemaDoc[]
     */
    public function generateForRequest(string $requestClass): array;

    public function formatComponentSchemaPath(string $schemaName): string;
}