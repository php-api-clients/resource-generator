<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use ApiClients\Tools\ResourceTestUtilities\AbstractEmptyResourceTest;
use ApiClients\Tools\ResourceTestUtilities\AbstractResourceTest;
use PhpParser\BuilderFactory;
use PhpParser\Node;

abstract class AbstractEmptyExtendingTestGenerator implements FileGeneratorInterface
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
        return $this->yaml['tests']['path'] .
            DIRECTORY_SEPARATOR .
            static::NAMESPACE .
            DIRECTORY_SEPARATOR .
            str_replace(
                '\\',
                DIRECTORY_SEPARATOR,
                $namespace . '\\' . $className
            ) .
            'Test.php'
        ;
    }

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $emptyClassChunks = explode('\\', $this->yaml['class']);
        $emptyBaseClass = array_pop($emptyClassChunks);
        $emptyClassName = 'Empty' . $emptyBaseClass;
        $emptyNamespace = $this->yaml['src']['namespace'] . '\\' . static::NAMESPACE;
        if (count($emptyClassChunks) > 0) {
            $emptyNamespace .= '\\' . implode('\\', $emptyClassChunks);
            $emptyNamespace = str_replace('\\\\', '\\', $emptyNamespace);
        }

        $classChunks = explode('\\', $this->yaml['class']);
        $baseClass = array_pop($classChunks);
        $className = $baseClass . 'Test';
        $namespace = $this->yaml['tests']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        $factory = new BuilderFactory;

        $class = $factory->class($className)
            ->makeFinal()
            ->extend('AbstractEmptyResourceTest');

        $class->addStmt($factory->method('getSyncAsync')
            ->makePublic()
            ->setReturnType('string')
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Scalar\String_(
                        static::NAMESPACE
                    )
                )
            ));

        $class->addStmt($factory->method('getClass')
            ->makePublic()
            ->setReturnType('string')
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\ClassConstFetch(
                        new Node\Name($emptyClassName),
                        'class'
                    )
                )
            ));

        return $factory->namespace($namespace)
            ->addStmt($factory->use(AbstractEmptyResourceTest::class)->as('AbstractEmptyResourceTest'))
            ->addStmt($factory->use($emptyNamespace . '\\' . $emptyClassName)->as($emptyClassName))
            ->addStmt($class)

            ->getNode()
        ;
    }
}
