<?php

$finder = new PhpCsFixer\Finder()
    ->exclude(__DIR__ . "/src/View")
    ->in(__DIR__ . "/src");

return new PhpCsFixer\Config()
    ->setRules([
        "@PSR12" => true,
        "braces_position" => [
            "allow_single_line_empty_anonymous_classes" => true,
            "allow_single_line_anonymous_functions" => true,
        ],
        "single_line_empty_body" => true,
    ])
    ->setFinder($finder);
