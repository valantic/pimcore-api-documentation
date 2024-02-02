<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Valantic\PimcoreApiDocumentationBundle\Http\Request\Contracts\HasDocsInfo;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

class RequestDecorator
{
    /**
     * @param class-string $requestClass
     */
    public function getDocsDescription(string $requestClass): string
    {
        return is_a($requestClass, HasDocsInfo::class, true)
            ? $requestClass::docsDescription()
            : Str::class_basename($requestClass);
    }
}
