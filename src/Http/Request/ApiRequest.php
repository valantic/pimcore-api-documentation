<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

abstract class ApiRequest
{
    public static function docsDescription(): string
    {
        return basename(str_replace('\\', '/', static::class));
    }
}
