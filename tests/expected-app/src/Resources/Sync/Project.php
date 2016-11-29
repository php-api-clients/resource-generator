<?php declare(strict_types=1);

namespace Example\Client\Resource\Sync;

use ApiClients\Foundation\Hydrator\CommandBus\Command\BuildAsyncFromSyncCommand;
use Example\Client\Resource\Project as BaseProject;

class Project extends BaseProject
{
    public function refresh() : Project
    {
        return $this->wait($this->handleCommand(new BuildAsyncFromSyncCommand('Project', $this)));
    }
}
