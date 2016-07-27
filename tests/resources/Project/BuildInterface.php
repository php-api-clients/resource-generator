<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use WyriHaximus\ApiClient\Resource\ResourceInterface;

interface BuildInterface extends ResourceInterface
{
    /**
     * @return int
     */
    public function id() : int;

    /**
     * @return string
     */
    public function name() : string;

    /**
     * @return string
     */
    public function description() : string;

    /**
     * @return array
     */
    public function config() : array;

    /**
     * @return SplObjectStorage
     */
    public function plugins() : SplObjectStorage;

    /**
     * @return DateTimeInterface
     */
    public function created() : DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function updated() : DateTimeInterface;

    /**
     * @return int
     */
    public function basicRate() : int;
}
