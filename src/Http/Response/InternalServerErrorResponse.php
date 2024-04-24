<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Valantic\PimcoreApiDocumentationBundle\Http\Dto\ErrorResponseDto;

class InternalServerErrorResponse extends AbstractApiResponse
{
    public function __construct(
        mixed $data = null,
        array $headers = [],
        bool $json = false,
    ) {
        if ($data === null) {
            $data = new ErrorResponseDto(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]);
        }

        parent::__construct($data, $headers, $json);
    }

    public static function status(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public static function getDtoClass(): string|false
    {
        return ErrorResponseDto::class;
    }
}
