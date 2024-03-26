<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response\Contracts;

interface HasDocsInfo
{
    public static function docsDescription(): string;
}
