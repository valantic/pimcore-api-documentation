<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Model\Doc\MethodDoc;

interface ControllerMethodParserInterface
{
    public function parseMethod(\ReflectionMethod $method): MethodDoc;
}
