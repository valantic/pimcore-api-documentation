<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Dto\Contracts;

interface HasDocsInfo
{
    public static function docsSchemaName(): string;
}
