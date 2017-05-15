<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Resource\ResourceInterface;
use DateTimeInterface;
use SplObjectStorage;

interface BuildInterface extends ResourceInterface
{
    const HYDRATE_CLASS = 'Project\\Build';

    /**
     * @return int
     */
    public function id(): int;

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function description(): string;

    /**
     * @return array
     */
    public function config(): array;

    /**
     * @return SplObjectStorage
     */
    public function plugins(): SplObjectStorage;

    /**
     * @return DateTimeInterface
     */
    public function createdAt(): DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function updatedAt(): DateTimeInterface;

    /**
     * @return int
     */
    public function basicRate(): int;
}
