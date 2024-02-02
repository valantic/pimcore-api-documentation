<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class AbstractApiResponse extends JsonResponse implements ApiResponseInterface
{
    /**
     * @param string[] $headers
     */
    public function __construct(
        mixed $data = null,
        array $headers = [],
        bool $json = false,
    ) {
        parent::__construct($data, static::status(), $headers, $json);
    }
}
