<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Model;

class PaginationDto
{
    public function __construct(
        public int $page,
        public int $perPage,
        public int $total,
    ) {}
}
