<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Valantic\PimcoreApiDocumentationBundle\Model\ErrorResponseDto;

class NotFoundResponse extends ApiResponse
{
    public function __construct(
        mixed $data = null,
        array $headers = [],
        bool $json = false,
    ) {
        if ($data === null) {
            $data = new ErrorResponseDto(Response::$statusTexts[Response::HTTP_NOT_FOUND]);
        }

        parent::__construct($data, $headers, $json);
    }

    public static function status(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public static function getDtoClass(): string|false
    {
        return ErrorResponseDto::class;
    }
}
