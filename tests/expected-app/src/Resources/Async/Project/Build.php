<?php declare(strict_types=1);

namespace Example\Client\Resource\Async\Project;

use Example\Client\Resource\Project\Build as BaseBuild;

class Build extends BaseBuild
{
    public function refresh(): Build
    {
        throw new \Exception('TODO: create refresh method!');
    }
}
