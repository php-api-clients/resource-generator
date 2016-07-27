<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use WyriHaximus\ApiClient\Annotations\Rename;
use WyriHaximus\ApiClient\Resource\TransportAwareTrait;

/**
 * @Rename(basic_rate="basic.rate")
 */
abstract class Build implements BuildInterface
{
    use TransportAwareTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var SplObjectStorage
     */
    protected $plugins;

    /**
     * @var DateTimeInterface
     */
    protected $created;

    /**
     * @var DateTimeInterface
     */
    protected $updated;

    /**
     * @var int
     */
    protected $basic_rate;

    /**
     * @return int
     */
    public function id() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function description() : string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function config() : array
    {
        return $this->config;
    }

    /**
     * @return SplObjectStorage
     */
    public function plugins() : SplObjectStorage
    {
        return $this->plugins;
    }

    /**
     * @return DateTimeInterface
     */
    public function created() : DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @return DateTimeInterface
     */
    public function updated() : DateTimeInterface
    {
        return $this->updated;
    }

    /**
     * @return int
     */
    public function basicRate() : int
    {
        return $this->basic_rate;
    }
}
