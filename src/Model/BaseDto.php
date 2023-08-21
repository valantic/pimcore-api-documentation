<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model;

abstract class BaseDto
{
    abstract public static function docsSchemaName(): string;
}
