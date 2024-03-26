<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Valantic\PimcoreApiDocumentationBundle\Http\Response\Contracts\HasDocsInfo;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

class ResponseDecorator
{
    /**
     * @param class-string $responseClass
     */
    public function getDocsDescription(string $responseClass): string
    {
        return is_a($responseClass, HasDocsInfo::class, true)
            ? $responseClass::docsDescription()
            : Str::class_basename($responseClass);
    }
}
