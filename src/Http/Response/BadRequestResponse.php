<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\Response;

class BadRequestResponse extends ApiResponse
{
    public static function status(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public static function getDtoClass(): string|false
    {
        return false;
    }
}
