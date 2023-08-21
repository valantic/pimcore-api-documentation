<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;

interface DocBlockParserInterface
{
    /**
     * @return PhpDocTagNode[]
     */
    public function parseDocBlock(string $docBlock): array;

    /**
     * @return string[]
     */
    public function determineTypeHint(PhpDocTagNode $docBlock, \ReflectionClass $reflectionClass): array;
}
