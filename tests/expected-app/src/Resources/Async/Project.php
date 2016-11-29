<?php declare(strict_types=1);

namespace Example\Client\Resource\Async;

use Example\Client\Resource\Project as BaseProject;

class Project extends BaseProject
{
    public function refresh() : Project
    {
        throw new \Exception('TODO: create refresh method!');
    }
}
