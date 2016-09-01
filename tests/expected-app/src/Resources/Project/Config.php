<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Resource\AbstractResource;

abstract class Config extends AbstractResource implements ConfigInterface
{
    /**
     * @var string
     */
    protected $a;

    /**
     * @var string
     */
    protected $b;

    /**
     * @var string
     */
    protected $c;

    /**
     * @var string
     */
    protected $d = 'abcd';

    /**
     * @return string
     */
    public function a() : string
    {
        return $this->a;
    }

    /**
     * @return string
     */
    public function b() : string
    {
        return $this->b;
    }

    /**
     * @return string
     */
    public function c() : string
    {
        return $this->c;
    }

    /**
     * @return string
     */
    public function d() : string
    {
        return $this->d;
    }
}
