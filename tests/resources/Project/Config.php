<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use WyriHaximus\ApiClient\Resource\TransportAwareTrait;

abstract class Config implements ConfigInterface
{
    use TransportAwareTrait;

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
    protected $d;

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
