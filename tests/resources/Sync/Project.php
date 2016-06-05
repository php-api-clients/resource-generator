<?php

namespace Example\Client\Resource\Sync;

use Example\Client\Resource\Project as BaseProject;

class Project extends BaseProject
{
    public function refresh() : Project
    {
        return $this->wait($this->callAsync('refresh'));
    }
}
