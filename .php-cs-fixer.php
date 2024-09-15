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
    'braces' => true,
    'cast_spaces' => true,
])->setFinder($finder);
