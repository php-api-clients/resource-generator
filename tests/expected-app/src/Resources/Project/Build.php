<?php declare(strict_types=1);

namespace Example\Client\Resource\Project;

use ApiClients\Foundation\Hydrator\Annotations\EmptyResource;
use ApiClients\Foundation\Hydrator\Annotations\Rename;
use ApiClients\Foundation\Resource\AbstractResource;
use DateTimeImmutable;
use DateTimeInterface;
use SplObjectStorage;

/**
 * @Rename(
 *     basic_rate="basic.rate"
 * )
 * @EmptyResource("Project\EmptyBuild")
 */
abstract class Build extends AbstractResource implements BuildInterface
{
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
     * @var DateTimeImmutable
     */
    protected $created_wrapped;

    /**
     * @var DateTimeInterface
     */
    protected $updated;

    /**
     * @var DateTimeImmutable
     */
    protected $updated_wrapped;

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
    public function createdAt() : DateTimeInterface
    {
        if ($this->created_wrapped instanceof DateTimeImmutable) {
            return $this->created_wrapped;
        }
        $this->created_wrapped = new DateTimeImmutable($this->created);
        return $this->created_wrapped;
    }

    /**
     * @return DateTimeInterface
     */
    public function updatedAt() : DateTimeInterface
    {
        if ($this->updated_wrapped instanceof DateTimeImmutable) {
            return $this->updated_wrapped;
        }
        $this->updated_wrapped = new DateTimeImmutable($this->updated);
        return $this->updated_wrapped;
    }

    /**
     * @return int
     */
    public function basicRate() : int
    {
        return $this->basic_rate;
    }
}
