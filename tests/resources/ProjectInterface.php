<?php

namespace Example\Client\Resource;

use WyriHaximus\ApiClient\Resource\ResourceInterface;
interface ProjectInterface extends ResourceInterface
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
    public function desription() : string;
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
}
