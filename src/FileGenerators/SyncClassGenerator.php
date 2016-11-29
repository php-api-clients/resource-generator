<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Hydrator\CommandBus\Command\BuildAsyncFromSyncCommand;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
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
        $namespace = $this->yaml['src']['namespace'] . '\\' . static::NAMESPACE;
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }
        $baseClass = $this->yaml['src']['namespace'] . '\\' . $this->yaml['class'];

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
                                new Node\Expr\Variable('this'),
                                'handleCommand',
                                [
                                    new Node\Expr\New_(
                                        new Node\Name('BuildAsyncFromSyncCommand'),
                                        [
                                            new Node\Scalar\String_($this->yaml['class']), // 👼 An angel for Michael https://twitter.com/michaelcullumuk/status/803599400685760512
                                            new Node\Expr\Variable('this'),
                                        ]
                                    ),
                                ]
                            ),
                        ]
                    )
                )
            ));

        return $factory->namespace($namespace)
            ->addStmt($factory->use(BuildAsyncFromSyncCommand::class))
            ->addStmt($factory->use($baseClass)->as('Base' . $className))
            ->addStmt($class)
            ->getNode()
            ;
    }
}
