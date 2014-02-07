<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude(array(
    ))
    ->notName('/.*\.(docx|xlsx|doc|xls|csv|ico|gif|png|jpeg|jpg|bmp|zip|gz|tar|7z|tiff|log|phar|jar|swf|fla)/')
    ->in(__DIR__)
;

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        'indentation',
        'linefeed',
        'trailing_spaces',
        'php_closing_tag',
        'short_tag',
        'return',
        'visibility',
        'braces',
        'phpdoc_params',
        'eof_ending',
        'extra_empty_lines',
        'include',
        'controls_spaces',
        'elseif',
        'psr0',
        'unused_use',
    ))
    ->finder($finder)
;
