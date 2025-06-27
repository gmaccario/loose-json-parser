<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
            '@Symfony' => true,
            'array_syntax' => ['syntax' => 'short'],
            'blank_line_after_opening_tag' => true,
            'blank_line_after_namespace' => true,
            'clean_namespace' => true,
//            'date_time_immutable' => true,
            'include' => true,
            'lowercase_keywords' => true,
            'multiline_whitespace_before_semicolons' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_empty_comment' => true,
            'no_leading_namespace_whitespace' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_space_around_double_colon' => true,
            'simplified_null_return' => true,
            'single_line_comment_style' => true,
            'single_line_after_imports' => true,
            'ternary_operator_spaces' => true,
    ])
    ->setFinder($finder)
;
