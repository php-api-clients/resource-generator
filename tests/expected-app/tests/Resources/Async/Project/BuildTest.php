<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Async\Project;

use ApiClients\Tools\ResourceTestUtilities\AbstractResourceTest;
use Example\Client\ApiSettings;
use Example\Client\Resource\Project\Build;

class BuildTest extends AbstractResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Async';
    }
    public function getClass() : string
    {
        return Build::class;
    }
    public function getNamespace() : string
    {
        return ApiSettings::NAMESPACE;
    }
}
