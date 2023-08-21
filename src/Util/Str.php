<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Util;

class Str
{
    public static function snake(string $string): string
    {
        if (!ctype_lower($string)) {
            $value = preg_replace('/\s+/u', '', ucwords($string));

            if (isset($value)) {
                $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);

                if (isset($value)) {
                    return static::lower($value);
                }
            }
        }

        return $string;
    }

    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }
}
