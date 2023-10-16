<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ApiResponse extends JsonResponse
{
    /**
     * @param string[] $headers
     */
    public function __construct(
        mixed $data,
        array $headers = [],
        bool $json = false,
    ) {
        parent::__construct($data, static::status(), $headers, $json);
    }

    abstract public static function status(): int;

    abstract public static function getDtoClass(): string|false;

    abstract public static function docsDescription(): string;
}