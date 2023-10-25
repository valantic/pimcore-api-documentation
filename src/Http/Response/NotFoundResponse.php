<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\Response;

class NotFoundResponse extends ApiResponse
{
    public static function status(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public static function getDtoClass(): string|false
    {
        return false;
    }

    public static function docsDescription(): string
    {
        return 'Not found response.';
    }
}
