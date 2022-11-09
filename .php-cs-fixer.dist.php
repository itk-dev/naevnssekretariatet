<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['var', 'node_modules'])
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@Symfony' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
    ])
    ->setFinder($finder)
;
