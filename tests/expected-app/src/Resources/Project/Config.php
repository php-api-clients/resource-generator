<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Hydrator\Annotation\EmptyResource;
use ApiClients\Foundation\Resource\AbstractResource;
use DateTimeZone;

/**
 * @EmptyResource("Project\EmptyConfig")
 */
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
     * @var string|null
     */
    protected $e;

    /**
     * @var string|DateTimeZone
     */
    protected $f;

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

    /**
     * @return string|null
     */
    public function e()
    {
        return $this->e;
    }

    /**
     * @return string|DateTimeZone
     */
    public function f()
    {
        return $this->f;
    }
}
