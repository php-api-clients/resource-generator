<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Resource\EmptyResourceInterface;
use DateTimeInterface;
use SplObjectStorage;

abstract class EmptyBuild implements BuildInterface, EmptyResourceInterface
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
    public function config() : array
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

    /**
     * @return int
     */
    public function basicRate() : int
    {
        return null;
    }
}
