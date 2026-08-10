<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Contract\Service;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;

interface DocBlockParserInterface
{
    /**
     * @return array<string, PhpDocChildNode>
     */
    public function parseDocBlock(string $docBlock, ?string $parameterName = null): array;

    /**
     * @return string[]
     */
    public function determineTypeHint(PhpDocChildNode $docBlock, \ReflectionClass $reflectionClass): array;
}
