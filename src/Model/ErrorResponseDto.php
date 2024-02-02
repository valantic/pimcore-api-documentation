<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model;

class ErrorResponseDto
{
    /**
     * @param string[] $errors
     */
    public function __construct(
        public string $message = 'Error response.',
        public array $errors = [],
    ) {}
}
