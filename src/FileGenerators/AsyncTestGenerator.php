<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;

final class AsyncTestGenerator extends AbstractExtendingTestGenerator implements FileGeneratorInterface
{
    const NAMESPACE = 'Async';
}
