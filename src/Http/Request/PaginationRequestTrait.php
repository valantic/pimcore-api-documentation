<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

trait PaginationRequestTrait
{
    public int $perPage = 20;
    public int $page = 1;
}
