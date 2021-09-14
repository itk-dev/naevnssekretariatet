<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['var', 'node_modules'])
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
