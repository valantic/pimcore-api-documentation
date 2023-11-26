<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;

interface ComponentPropertyDocGeneratorInterface
{
    public function generate(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc;
}
