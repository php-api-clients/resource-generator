<?php declare(strict_types=1);

namespace Example\Client\Resource;

use ApiClients\Foundation\Resource\EmptyResourceInterface;
use DateTimeInterface;
use SplObjectStorage;

abstract class EmptyProject implements ProjectInterface, EmptyResourceInterface
{
    /**
     * @return int
     */
    public function id() : int
    {
        return null;
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return null;
    }

    /**
     * @return string
     */
    public function description() : string
    {
        return null;
    }

    /**
     * @return array
     */
    public function builds() : array
    {
        return null;
    }

    /**
     * @return Project\Build
     */
    public function latestBuild() : Project\Build
    {
        return null;
    }

    /**
     * @return Project\Config
     */
    public function config() : Project\Config
    {
        return null;
    }

    /**
     * @return SplObjectStorage
     */
    public function plugins() : SplObjectStorage
    {
        return null;
    }

    /**
     * @return DateTimeInterface
     */
    public function createdAt() : DateTimeInterface
    {
        return null;
    }

    /**
     * @return DateTimeInterface
     */
    public function updatedAt() : DateTimeInterface
    {
        return null;
    }
}
