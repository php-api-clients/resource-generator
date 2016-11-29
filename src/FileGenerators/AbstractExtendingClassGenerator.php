<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use PhpParser\Node;

abstract class AbstractExtendingClassGenerator implements FileGeneratorInterface
{
    /**
     * @var array
     */
    protected $yaml;

    /**
     * InterfaceGenerator constructor.
     * @param array $yaml
     */
    public function __construct(array $yaml)
    {
        $this->yaml = $yaml;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->yaml['src']['path'] .
            DIRECTORY_SEPARATOR .
            static::NAMESPACE .
            DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $this->yaml['class']) .
            '.php'
        ;
    }
}
