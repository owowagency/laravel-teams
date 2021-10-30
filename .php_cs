<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';

return (new MattAllan\LaravelCodeStyle\Config())
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/config')
            ->in(__DIR__.'/tests')
    )
    ->setRules([
        '@Laravel' => true,
        'method_chaining_indentation' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_types_order' => true,
    ]);
