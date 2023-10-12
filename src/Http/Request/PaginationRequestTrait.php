<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

trait PaginationRequestTrait
{
    public int $perPage = 20;

    public int $page = 1;
}
