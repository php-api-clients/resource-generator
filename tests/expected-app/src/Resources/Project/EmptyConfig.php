<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Resource\EmptyResourceInterface;

abstract class EmptyConfig implements ConfigInterface, EmptyResourceInterface
{
    /**
     * @return string
     */
    public function a() : string
    {
        return null;
    }

    /**
     * @return string
     */
    public function b() : string
    {
        return null;
    }

    /**
     * @return string
     */
    public function c() : string
    {
        return null;
    }

    /**
     * @return string
     */
    public function d() : string
    {
        return null;
    }
}
