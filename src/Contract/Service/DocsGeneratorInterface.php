<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

interface DocsGeneratorInterface
{
    public function generate(string $docsPath): void;
}
