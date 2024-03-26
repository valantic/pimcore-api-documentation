<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

interface ApiResponseInterface
{
    public static function status(): int;

    /**
     * @return class-string|false
     */
    public static function getDtoClass(): string|false;
}
