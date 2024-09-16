<?php

$finder = PhpCsFixer\Finder::create()
           ->in([
               __DIR__ . '/src',
               __DIR__ . '/config',
               __DIR__ . '/tests',
           ]);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'new_with_parentheses' => false,
    'blank_line_after_namespace' => true,
    'blank_line_after_opening_tag' => true,
    'blank_line_before_statement' => [
        'statements' => ['return'],
    ],
    'no_extra_blank_lines' => true,
    'no_trailing_whitespace' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
    ],
    'single_quote' => true,
    'trailing_comma_in_multiline' => true,
    'no_empty_phpdoc' => true,
    'single_blank_line_at_eof' => true,
    'ternary_operator_spaces' => true,
    'no_useless_return' => true,
    'braces' => true,
    'cast_spaces' => true,
    'no_unused_imports' => true,
    'fully_qualified_strict_types' => true,
    'ordered_class_elements' => true,
])->setFinder($finder);
