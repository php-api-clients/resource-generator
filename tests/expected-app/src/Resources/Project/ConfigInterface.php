<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Resource\ResourceInterface;

interface ConfigInterface extends ResourceInterface
{
    const HYDRATE_CLASS = 'Project\\Config';

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
