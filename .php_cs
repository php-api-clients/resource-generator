<?php declare(strict_types=1);

use ApiClients\Tools\ResourceGenerator\EmptyLineAboveDocblocksFixer;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'ordered_imports' => true,
        'ordered_class_elements' => true,
        'single_blank_line_before_namespace' => true,
        'method_separation' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'single_class_element_per_statement' => true,
        'ApiClients/empty_line_above_docblocks' => true,
        'no_extra_consecutive_blank_lines' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->registerCustomFixers([
        new EmptyLineAboveDocblocksFixer(),
    ])
;
