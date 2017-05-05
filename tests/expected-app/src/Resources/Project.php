<?php declare(strict_types=1);

namespace Example\Client\Resource;

use ApiClients\Foundation\Hydrator\Annotation\Collection;
use ApiClients\Foundation\Hydrator\Annotation\EmptyResource;
use ApiClients\Foundation\Hydrator\Annotation\Nested;
use ApiClients\Foundation\Resource\AbstractResource;
use DateTimeImmutable;
use DateTimeInterface;
use SplObjectStorage;

/**
 * @Collection(
 *     builds="Project\Build"
 * )
 * @Nested(
 *     latestBuild="Project\Build",
 *     config="Project\Config"
 * )
 * @EmptyResource("EmptyProject")
 */
abstract class Project extends AbstractResource implements ProjectInterface
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
    protected $builds;

    /**
     * @var Project\Build
     */
    protected $latestBuild;

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
    public function builds() : array
    {
        return $this->builds;
    }

    /**
     * @return Project\Build
     */
    public function latestBuild() : Project\Build
    {
        return $this->latestBuild;
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
}
