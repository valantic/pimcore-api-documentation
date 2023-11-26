<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use Valantic\PimcoreApiDocumentationBundle\Model\Component\Property\AbstractPropertyDoc;

/**
 * @template T of AbstractPropertyDoc
 */
interface DataTypeParserInterface
{
    /**
     * @return T
     */
    public function parse(\ReflectionProperty $reflectionProperty): AbstractPropertyDoc;

    public function supports(\ReflectionProperty $reflectionProperty): bool;
}
