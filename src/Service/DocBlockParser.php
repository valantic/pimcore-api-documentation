<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use Doctrine\Common\Annotations\PhpParser;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type as PhpStanType;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;

class DocBlockParser implements DocBlockParserInterface
{
    private PhpParser $phpParser;
    private Lexer $lexer;
    private PhpDocParser $phpDocParser;

    public function __construct()
    {
        $this->phpParser = new PhpParser();
        $this->lexer = new Lexer();

        $constExprParser = new ConstExprParser();
        $typeParser = new TypeParser($constExprParser);

        $this->phpDocParser = new PhpDocParser($typeParser, $constExprParser);
    }

    public function parseDocBlock(string $docBlock): array
    {
        $docBlockData = [];
        $tokens = new TokenIterator($this->lexer->tokenize($docBlock));
        $parsedDocBlock = $this->phpDocParser->parse($tokens);

        foreach ($parsedDocBlock->children as $docBlockItem) {
            if ($docBlockItem->value instanceof ParamTagValueNode) {
                $parameterName = ltrim($docBlockItem->value->parameterName, '$');
            }

            if ($docBlockItem->value instanceof VarTagValueNode) {
                $parameterName = ltrim($docBlockItem->value->variableName, '$');
            }

            if (isset($parameterName)) {
                $docBlockData[$parameterName] = $docBlockItem;
            }
        }

        return $docBlockData;
    }

    public function determineTypeHint(PhpDocTagNode $docBlock, \ReflectionClass $reflectionClass): array
    {
        $useStatements = $this->phpParser->parseUseStatements($reflectionClass);

        $typeHint = $docBlock->value->type->type->name;

        if (array_key_exists(strtolower($typeHint), $useStatements)) {
            return [$useStatements[strtolower($typeHint)]];
        }

        $classTypeHint = sprintf('%s\\%s', $reflectionClass->getNamespaceName(), $typeHint);
        if (class_exists($classTypeHint)) {
            return [$classTypeHint];
        }

        return [];
    }
}
