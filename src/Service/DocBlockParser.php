<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Doctrine\Common\Annotations\PhpParser;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\TypeEnum;

class DocBlockParser implements DocBlockParserInterface
{
    private readonly PhpParser $phpParser;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $phpDocParser;

    public function __construct()
    {
        $this->phpParser = new PhpParser();
        $this->lexer = new Lexer();

        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);

        $this->phpDocParser = new PhpDocParser($typeParser, $constExprParser);
    }

    public function parseDocBlock(string $docBlock, ?string $parameterName = null): array
    {
        $docBlockData = [];
        $tokens = new TokenIterator($this->lexer->tokenize($docBlock));
        $parsedDocBlock = $this->phpDocParser->parse($tokens);

        foreach ($parsedDocBlock->children as $docBlockItem) {
            $docBlockParamName = $parameterName;

            if ($docBlockItem->value instanceof ParamTagValueNode) {
                $docBlockParamName = ltrim($docBlockItem->value->parameterName, '$');
            }

            if ($docBlockItem->value instanceof VarTagValueNode) {
                $docBlockParamName = ltrim($docBlockItem->value->variableName, '$');
            }

            if (isset($docBlockParamName) && $docBlockParamName !== '') {
                $docBlockData[$docBlockParamName] = $docBlockItem;
            }
        }

        return $docBlockData;
    }

    public function determineTypeHint(PhpDocChildNode $docBlock, \ReflectionClass $reflectionClass): array
    {
        $useStatements = $this->phpParser->parseUseStatements($reflectionClass);

        $typeHint = $docBlock->value->type->type->name ?? null;

        if ($typeHint === null) {
            return [];
        }

        if (array_key_exists(strtolower((string) $typeHint), $useStatements)) {
            return [$useStatements[strtolower((string) $typeHint)]];
        }

        $classTypeHint = sprintf('%s\\%s', $reflectionClass->getNamespaceName(), $typeHint);

        if (class_exists($classTypeHint)) {
            return [$classTypeHint];
        }

        if (TypeEnum::tryFrom($typeHint)) {
            return [TypeEnum::tryFrom($typeHint)->swaggerEnum()];
        }

        return [];
    }
}
