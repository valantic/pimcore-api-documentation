<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Enum;

enum TypeEnum: string
{
    case ARRAY = 'array';
    case INT = 'int';
    case STRING = 'string';

    public function swaggerEnum(): string
    {
        return match ($this) {
            self::ARRAY => 'array',
            self::INT => 'integer',
            self::STRING => 'string',
        };
    }
}
