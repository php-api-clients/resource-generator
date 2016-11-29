<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node;

final class AsyncClassGenerator extends AbstractExtendingClassGenerator implements FileGeneratorInterface
{
    const NAMESPACE = 'Async';

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

        $factory = new BuilderFactory();

        $class = $factory->class($className)
            ->extend('Base' . $className);

        $class->addStmt(
            $factory->method('refresh')
                ->makePublic()
                ->setReturnType($className)
                ->addStmt(
                    new Node\Stmt\Throw_(
                        new Node\Expr\New_(
                            new Node\Name('\Exception'),
                            [
                                new Node\Scalar\String_(
                                    'TODO: create refresh method!'
                                )
                            ]
                        )
                    )
                )
        );

        return $factory->namespace($namespace)
            ->addStmt($factory->use($baseClass)->as('Base' . $className))
            ->addStmt($class)
            ->getNode()
            ;
    }
}
