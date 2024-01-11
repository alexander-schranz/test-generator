<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->notName('*_*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'ordered_imports' => true,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_align' => ['align' => 'left'],
        'class_definition' => [
            'multi_line_extends_each_single_line' => true,
        ],
        'linebreak_after_opening_tag' => true,
        'declare_strict_types' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_constant_invocation' => true,
        'native_function_casing' => true,
        'native_function_invocation' => ['include' => ['@internal']],
        'no_php4_constructor' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'php_unit_strict' => true,
        'phpdoc_order' => true,
        'semicolon_after_instruction' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'array_indentation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'single_line_throw' => false,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],
        'nullable_type_declaration_for_default_null_value' => true,
        'no_null_property_initialization' => false,
        'fully_qualified_strict_types' => [
            'leading_backslash_in_global_namespace' => true,
        ],
    ])
    ->setFinder($finder);
