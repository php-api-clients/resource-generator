<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Sync;

use ApiClients\Tools\ResourceTestUtilities\AbstractEmptyResourceTest;
use Example\Client\Resource\Sync\EmptyProject;

final class EmptyProjectTest extends AbstractEmptyResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Sync';
    }
    public function getClass() : string
    {
        return EmptyProject::class;
    }
}
