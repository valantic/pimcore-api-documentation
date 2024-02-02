<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model\Contracts;

interface HasDocsInfo
{
    public static function docsSchemaName(): string;
}
