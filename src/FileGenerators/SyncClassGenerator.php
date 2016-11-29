<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Hydrator\CommandBus\Command\BuildAsyncFromSyncCommand;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\BuilderFactory;
use PhpParser\Node;

final class SyncClassGenerator extends AbstractExtendingClassGenerator implements FileGeneratorInterface
{
    const NAMESPACE = 'Sync';

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $classChunks = explode('\\', $this->yaml['class']);
        $className = array_pop($classChunks);
        $interfaceName = $className . 'Interface';
        $namespace = $this->yaml['src']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }
        $baseClass = $this->yaml['src']['namespace'] . '\\' . $this->yaml['class'];
        $interfaceFQName = $this->yaml['src']['namespace'] . '\\' . $this->yaml['class'] . 'Interface';

        $factory = new BuilderFactory();

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
                                $this->callCommandBus(),
                                'then',
                                [
                                   $this->createRefreshClosure($className, $interfaceName),
                                ]
                            ),
                        ]
                    )
                )
            ));

        return $factory->namespace($namespace)
            ->addStmt($factory->use(BuildAsyncFromSyncCommand::class))
            ->addStmt($factory->use($baseClass)->as('Base' . $className))
            ->addStmt($factory->use($interfaceFQName))
            ->addStmt($class)
            ->getNode()
            ;
    }

    protected function callCommandBus(): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(
            new Node\Expr\Variable('this'),
            'handleCommand',
            [
                $this->createCommand(),
            ]
        );
    }

    protected function createCommand(): Node\Expr\New_
    {
        return new Node\Expr\New_(
            new Node\Name('BuildAsyncFromSyncCommand'),
            [
                new Node\Expr\ClassConstFetch(
                    new Node\Name('self'),
                    'HYDRATE_CLASS'
                ),
                new Node\Expr\Variable('this'),
            ]
        );
    }

    protected function createRefreshClosure(string $className, string $interfaceName): Node\Expr\Closure
    {
        return new Node\Expr\Closure(
            [
                'params' => [
                    new Node\Param(
                        Inflector::camelize($className),
                        null,
                        $interfaceName
                    )
                ],
                'stmts' => [
                    new Node\Stmt\Return_(
                        new Node\Expr\MethodCall(
                            new Node\Expr\Variable(
                                Inflector::camelize($className)
                            ),
                            'refresh'
                        )
                    )
                ],
            ]
        );
    }
}
