<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        mongoDb: true,
        gedmo: true,
        phpunit: true,
        fosRest: true,
        jms: true,
        sensiolabs: true,
    )
    ->withPhpSets()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRootFiles()
    ->withSkip([
        CountArrayToEmptyArrayComparisonRector::class,
        ShortenElseIfRector::class,
        NewlineAfterStatementRector::class,
        DisallowedShortTernaryRuleFixerRector::class,
    ]);
