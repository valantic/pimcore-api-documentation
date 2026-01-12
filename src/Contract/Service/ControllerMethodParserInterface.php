<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Model\Doc\MethodDoc;

interface ControllerMethodParserInterface
{
    /**
     * @return MethodDoc[]
     */
    public function parseMethod(\ReflectionMethod $method): array;
}
