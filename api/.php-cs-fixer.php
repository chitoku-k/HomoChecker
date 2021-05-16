<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '|' => 'no_space',
            ],
        ],
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => [],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_chaining_indentation' => false,
        'multiline_whitespace_before_semicolons' => false,
        'no_superfluous_phpdoc_tags' => true,
        'no_unneeded_curly_braces' => false,
        'ordered_class_elements' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => false,
        'single_blank_line_before_namespace' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
