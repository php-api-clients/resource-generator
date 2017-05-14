<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node;

abstract class AbstractEmptyExtendingClassGenerator implements FileGeneratorInterface
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
        $classChunks = explode('\\', $this->yaml['class']);
        $className = array_pop($classChunks);
        $className = 'Empty' . $className;
        $namespace = '';
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        return $this->yaml['src']['path'] .
            DIRECTORY_SEPARATOR .
            static::NAMESPACE .
            DIRECTORY_SEPARATOR .
            str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                $namespace . '\\' . $className
            ) .
            '.php'
        ;
    }

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $classChunks = explode('\\', $this->yaml['class']);
        $baseClass = array_pop($classChunks);
        $className = 'Empty' . $baseClass;
        $baseNamespace = $this->yaml['src']['namespace'];
        if (count($classChunks) > 0) {
            $baseNamespace .= '\\' . implode('\\', $classChunks);
            $baseNamespace = str_replace('\\\\', '\\', $baseNamespace);
        }
        $namespace = $this->yaml['src']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        $factory = new BuilderFactory();

        $stmt = $factory->namespace($namespace)->
            addStmt($factory->use($baseNamespace . '\\' . $className)->as('Base' . $className))->
            addStmt(
                $factory->class($className)
                ->extend('Base' . $className)
            )
        ;

        return $stmt
            ->getNode()
        ;
    }
}
