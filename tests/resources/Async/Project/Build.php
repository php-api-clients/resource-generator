<?php

namespace Example\Client\Resource\Async\Project;

use Example\Client\Resource\Project\Build as BaseBuild;

class Build extends BaseBuild
{
    public function refresh() : Build
    {
        return $this->wait($this->callAsync('refresh'));
    }
}
