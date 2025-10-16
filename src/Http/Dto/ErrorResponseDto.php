<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Dto;

class ErrorResponseDto
{
    /**
     * @param string[] $errors
     */
    public function __construct(
        public string $message = 'Error response.',
        public array $errors = [],
    ) {
    }
}
