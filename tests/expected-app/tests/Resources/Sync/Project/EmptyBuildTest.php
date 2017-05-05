<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Sync\Project;

use ApiClients\Tools\ResourceTestUtilities\AbstractEmptyResourceTest;
use Example\Client\Resource\Sync\Project\EmptyBuild;

final class EmptyBuildTest extends AbstractEmptyResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Sync';
    }
    public function getClass() : string
    {
        return EmptyBuild::class;
    }
}
