<?php declare(strict_types=1);

namespace Example\Client\Resource\Async\Project;

use Example\Client\Resource\Project\Config as BaseConfig;

class Config extends BaseConfig
{
    public function refresh() : Config
    {
        throw new \Exception('TODO: create refresh method!');
    }
}
