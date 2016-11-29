<?php declare(strict_types=1);

namespace Example\Client\Resource\Sync\Project;

use ApiClients\Foundation\Hydrator\CommandBus\Command\BuildAsyncFromSyncCommand;
use Example\Client\Resource\Project\Build as BaseBuild;
use Example\Client\Resource\Project\BuildInterface;

class Build extends BaseBuild
{
    public function refresh() : Build
    {
        return $this->wait($this->handleCommand(new BuildAsyncFromSyncCommand(self::HYDRATE_CLASS, $this))->then(function (BuildInterface $build) {
            return $build->refresh();
        }));
    }
}
