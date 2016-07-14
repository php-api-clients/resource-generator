<?php declare(strict_types=1);

namespace Example\Client\Resource;

use WyriHaximus\ApiClient\Annotations\Nested;
use WyriHaximus\ApiClient\Resource\TransportAwareTrait;

/**
 * @Nested(build="Project\Build", config="Project\Config")
 */
abstract class Project implements ProjectInterface
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
     * @var Project\Build
     */
    protected $build;

    /**
     * @var Project\Config
     */
    protected $config;

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
     * @return Project\Build
     */
    public function build() : Project\Build
    {
        return $this->build;
    }

    /**
     * @return Project\Config
     */
    public function config() : Project\Config
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
}
