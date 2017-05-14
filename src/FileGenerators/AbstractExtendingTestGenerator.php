<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use ApiClients\Tools\ResourceTestUtilities\AbstractResourceTest;
use PhpParser\BuilderFactory;
use PhpParser\Node;

abstract class AbstractExtendingTestGenerator implements FileGeneratorInterface
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
        return $this->yaml['tests']['path'] .
            DIRECTORY_SEPARATOR .
            static::NAMESPACE .
            DIRECTORY_SEPARATOR .
            str_replace('\\', DIRECTORY_SEPARATOR, $this->yaml['class']) .
            'Test.php'
        ;
    }

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $classChunks = explode('\\', $this->yaml['class']);
        $baseClass = array_pop($classChunks);
        $className = $baseClass . 'Test';
        $namespace = $this->yaml['tests']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }
        $baseClassFQCN = $this->yaml['src']['namespace'] . '\\' . $this->yaml['class'];

        $factory = new BuilderFactory();

        $class = $factory->class($className)
            ->extend('AbstractResourceTest');

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
                        new Node\Name($baseClass),
                        'class'
                    )
                )
            ));

        $class->addStmt($factory->method('getNamespace')
            ->makePublic()
            ->setReturnType('string')
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\ClassConstFetch(
                        new Node\Name('ApiSettings'),
                        'NAMESPACE'
                    )
                )
            ));

        return $factory->namespace($namespace)
            ->addStmt($factory->use(AbstractResourceTest::class)->as('AbstractResourceTest'))
            ->addStmt($factory->use($this->yaml['api_settings'])->as('ApiSettings'))
            ->addStmt($factory->use($baseClassFQCN)->as($baseClass))
            ->addStmt($class)

            ->getNode()
        ;
    }
}
