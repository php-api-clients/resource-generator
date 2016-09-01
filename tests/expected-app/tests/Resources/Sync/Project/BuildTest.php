<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Sync\Project;

use ApiClients\Tools\ResourceTestUtilities\AbstractResourceTest;
use Example\Client\ApiSettings;
use Example\Client\Resource\Project\Build;

class BuildTest extends AbstractResourceTest
{
    public function getSyncAsync() : string
    {
        return 'Sync';
    }
    public function getClass() : string
    {
        return Build::class;
    }
    public function getNamespace() : string
    {
        return Apisettings::NAMESPACE;
    }
}
