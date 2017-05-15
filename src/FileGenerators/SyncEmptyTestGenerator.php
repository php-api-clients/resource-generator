<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;

final class SyncEmptyTestGenerator extends AbstractEmptyExtendingTestGenerator implements FileGeneratorInterface
{
    const NAMESPACE = 'Sync';
}
