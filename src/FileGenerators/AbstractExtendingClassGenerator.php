<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use PhpParser\BuilderFactory;
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

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $classChunks = explode('\\', $this->yaml['class']);
        $className = array_pop($classChunks);
        $namespace = $this->yaml['src']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }
        $baseClass = $this->yaml['src']['namespace'] . '\\' . $this->yaml['class'];

        $factory = new BuilderFactory;

        $class = $factory->class($className)
            ->extend('Base' . $className);

        $class->addStmt($factory->method('refresh')
            ->makePublic()
            ->setReturnType($className)
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\MethodCall(
                        new Node\Expr\Variable('this'),
                        'wait',
                        [
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('this'),
                                'callAsync',
                                [
                                    new Node\Scalar\String_('refresh'),
                                ]
                            ),
                        ]
                    )
                )
            ));

        return $factory->namespace($namespace)
            ->addStmt($factory->use($baseClass)->as('Base' . $className))
            ->addStmt($class)
            ->getNode()
        ;
    }
}
