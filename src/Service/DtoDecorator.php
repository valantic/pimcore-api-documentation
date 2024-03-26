<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Valantic\PimcoreApiDocumentationBundle\Http\Dto\Contracts\HasDocsInfo;
use Valantic\PimcoreApiDocumentationBundle\Util\Str;

class DtoDecorator
{
    /**
     * @param class-string $dtoClass
     */
    public function getDocsDescription(string $dtoClass): string
    {
        return is_a($dtoClass, HasDocsInfo::class, true)
            ? $dtoClass::docsSchemaName()
            : Str::class_basename($dtoClass);
    }
}
