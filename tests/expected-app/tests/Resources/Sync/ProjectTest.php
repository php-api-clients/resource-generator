<?php declare(strict_types=1);

namespace Example\Tests\Client\Resource\Sync;

use ApiClients\Tools\ResourceTestUtilities\AbstractResourceTest;
use Example\Client\ApiSettings;
use Example\Client\Resource\Project;

class ProjectTest extends AbstractResourceTest
{
    public function getSyncAsync(): string
    {
        return 'Sync';
    }

    public function getClass(): string
    {
        return Project::class;
    }

    public function getNamespace(): string
    {
        return ApiSettings::NAMESPACE;
    }
}
