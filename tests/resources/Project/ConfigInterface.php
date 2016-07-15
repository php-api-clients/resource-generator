<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use WyriHaximus\ApiClient\Resource\ResourceInterface;

interface ConfigInterface extends ResourceInterface
{
    /**
     * @return string
     */
    public function a() : string;

    /**
     * @return string
     */
    public function b() : string;

    /**
     * @return string
     */
    public function c() : string;

    /**
     * @return string
     */
    public function d() : string;
}
