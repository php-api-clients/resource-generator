<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Async;

use ApiClients\Tools\ResourceTestUtilities\AbstractEmptyResourceTest;
use Example\Client\Resource\Async\EmptyProject;

final class EmptyProjectTest extends AbstractEmptyResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Async';
    }

    public function getClass() : string
    {
        return EmptyProject::class;
    }
}
