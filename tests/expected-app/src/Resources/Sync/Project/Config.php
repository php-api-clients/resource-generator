<?php declare(strict_types=1);

namespace Example\Client\Resource\Sync\Project;

use ApiClients\Foundation\Hydrator\CommandBus\Command\BuildAsyncFromSyncCommand;
use Example\Client\Resource\Project\Config as BaseConfig;

class Config extends BaseConfig
{
    public function refresh() : Config
    {
        return $this->wait($this->handleCommand(new BuildAsyncFromSyncCommand('Project\\Config', $this)));
    }
}
