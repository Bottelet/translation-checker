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
])->setFinder($finder);
