<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/.ci',
        __DIR__ . '/config',
        __DIR__ . '/src',
    ])
    ->name(['*.php', '*.stub']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PER' => true,
        '@PER:risky' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        'array_indentation' => true,
        'array_push' => false,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                // 'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
                'yield_from',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'increment_style' => [
            'style' => 'post',
        ],
        'multiline_whitespace_before_semicolons' => true,
        'native_constant_invocation' => false,
        'native_function_invocation' => false,
        'no_empty_comment' => true,
        'no_useless_return' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'case',
                'property_public',
                'property_public_static',
                'property_protected',
                'property_protected_static',
                'property_private',
                'property_private_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_public_abstract',
                'method_public_static',
                'method_protected',
                'method_protected_abstract',
                'method_protected_static',
                'method_private',
            ],
        ],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_summary' => false,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'self_accessor' => false, // do not enable self_accessor as it breaks pimcore models relying on get_called_class()
        'single_line_throw' => false,
        'strict_comparison' => true,
        'strict_param' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'parameters', 'match']],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder);
