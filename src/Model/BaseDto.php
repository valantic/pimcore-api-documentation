<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model;

abstract class BaseDto
{
    public static function docsSchemaName(): string
    {
        return basename(str_replace('\\', '/', static::class));
    }
}
