<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Enum;

enum DataTypeEnum: string
{
    case ARRAY = 'array';
    case INTEGER = 'integer';
    case STRING = 'string';
    case FLOAT = 'number';
    case BOOLEAN = 'boolean';
}
