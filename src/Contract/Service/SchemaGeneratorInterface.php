<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Http\Request\ApiRequest;
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
     * @param class-string<ApiRequest> $requestClass
     */
    public function generateForRequest(string $requestClass): ComponentSchemaDoc;

    public function formatComponentSchemaPath(string $schemaName): string;
}
