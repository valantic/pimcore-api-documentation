<?php

declare(strict_types=1);

namespace Valantic\PimcoreApiDocumentationBundle\Service;

use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use Valantic\PimcoreApiDocumentationBundle\Contract\Service\DocBlockParserInterface;
use Valantic\PimcoreApiDocumentationBundle\Enum\TypeEnum;

class DocBlockParser implements DocBlockParserInterface
{
    private readonly Lexer $lexer;
    private readonly PhpDocParser $phpDocParser;

    public function __construct()
    {
        $config = new ParserConfig([]);
        $this->lexer = new Lexer($config);

        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);

        $this->phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
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

    public function determineTypeHint(PhpDocChildNode $docBlock, \ReflectionClass $reflectionClass): array
    {
        $useStatements = $this->parseUseStatements($reflectionClass);

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

        if (TypeEnum::tryFrom($typeHint) instanceof TypeEnum) {
            return [TypeEnum::tryFrom($typeHint)->swaggerEnum()];
        }

        return [];
    }

    /**
     * Parse use statements from a class file using nikic/php-parser.
     *
     * @return array<string, string> Map of lowercase alias to fully qualified class name
     */
    private function parseUseStatements(\ReflectionClass $reflectionClass): array
    {
        $fileName = $reflectionClass->getFileName();

        if ($fileName === false) {
            return [];
        }

        $content = file_get_contents($fileName);

        if ($content === false) {
            return [];
        }

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $stmts = $parser->parse($content);
        } catch (\PhpParser\Error) {
            return [];
        }

        if ($stmts === null) {
            return [];
        }

        return $this->extractUseStatements($stmts);
    }

    /**
     * Recursively extract use statements from AST.
     *
     * Handles:
     * - Regular use statements: use Foo\Bar;
     * - Aliased use statements: use Foo\Bar as Baz;
     * - Grouped use statements: use Foo\{Bar, Baz as B};
     *
     * @param \PhpParser\Node\Stmt[] $stmts
     *
     * @return array<string, string> Map of lowercase alias to fully qualified class name
     */
    private function extractUseStatements(array $stmts): array
    {
        $useStatements = [];

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                // Recurse into namespace
                $useStatements = array_merge($useStatements, $this->extractUseStatements($stmt->stmts));
            } elseif ($stmt instanceof Use_) {
                // Regular use statement: use Foo\Bar; or use Foo\Bar as Baz;
                // Note: This only handles class imports (TYPE_NORMAL or TYPE_UNKNOWN)
                // and filters out 'use function' and 'use const'
                if ($stmt->type === Use_::TYPE_NORMAL || $stmt->type === Use_::TYPE_UNKNOWN) {
                    foreach ($stmt->uses as $use) {
                        $fullName = $use->name->toString();
                        $alias = $use->alias !== null ? $use->alias->toString() : $use->name->getLast();
                        $useStatements[strtolower($alias)] = $fullName;
                    }
                }
            } elseif ($stmt instanceof GroupUse) {
                // Grouped use statement: use Foo\{Bar, Baz as B};
                // TYPE_UNKNOWN is used when no explicit type is specified (default for class imports)
                if ($stmt->type === Use_::TYPE_NORMAL || $stmt->type === Use_::TYPE_UNKNOWN) {
                    $prefix = $stmt->prefix->toString();

                    foreach ($stmt->uses as $use) {
                        $fullName = $prefix . '\\' . $use->name->toString();
                        $alias = $use->alias !== null ? $use->alias->toString() : $use->name->getLast();
                        $useStatements[strtolower($alias)] = $fullName;
                    }
                }
            }
        }

        return $useStatements;
    }
}
