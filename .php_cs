<?php declare(strict_types=1);

use ApiClients\Tools\ResourceGenerator\EmptyLineAboveDocblocksFixer;
use ApiClients\Tools\TestUtilities\PhpCsFixerConfig;

return (function ()
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'src';

    return PhpCsFixerConfig::create([
        'ApiClients/empty_line_above_docblocks' => true,
    ])
        ->setFinder(
            PhpCsFixer\Finder::create()
                ->in($path)
                ->append([$path])
            )
        ->setUsingCache(false)
        ->registerCustomFixers([
            new EmptyLineAboveDocblocksFixer(),
        ])
    ;
})();

