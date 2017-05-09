<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Async\Project;

use ApiClients\Tools\ResourceTestUtilities\AbstractEmptyResourceTest;
use Example\Client\Resource\Async\Project\EmptyConfig;

final class EmptyConfigTest extends AbstractEmptyResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Async';
    }

    public function getClass() : string
    {
        return EmptyConfig::class;
    }
}
