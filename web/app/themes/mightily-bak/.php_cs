<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('somedir')
    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces' => ['position_after_functions_and_oop_constructs' => 'same'],
        // 'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        // 'elseif' => true,
        'function_declaration' => ['closure_function_spacing' => 'one'],
        // 'semicolon_after_instruction' => true,
        'single_quote' => ['strings_containing_single_quote_chars' => true],
        // 'strict_param' => true,
        // 'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder)
;
