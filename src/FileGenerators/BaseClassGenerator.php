<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Hydrator\Annotations\Collection;
use ApiClients\Foundation\Hydrator\Annotations\Nested;
use ApiClients\Foundation\Hydrator\Annotations\Rename;
use ApiClients\Foundation\Resource\AbstractResource;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node;

final class BaseClassGenerator implements FileGeneratorInterface
{
    /**
     * @var array
     */
    protected $yaml;

    /**
     * @var BuilderFactory
     */
    protected $factory;

    /**
     * @var string[]
     */
    protected $docBlock = [];

    /**
     * @var array
     */
    protected $uses = [
        AbstractResource::class => true,
    ];

    /**
     * InterfaceGenerator constructor.
     * @param array $yaml
     */
    public function __construct(array $yaml)
    {
        $this->yaml = $yaml;
        if (isset($this->yaml['uses']) && is_array($this->yaml['uses'])) {
            $this->uses += $this->yaml['uses'];
        }
        $this->factory = new BuilderFactory();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->yaml['src']['path'] .
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
        $namespace = $this->yaml['src']['namespace'];
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        $class = $this->factory->class($className)
            ->implement($className . 'Interface')
            ->extend('AbstractResource')
            ->makeAbstract();

        $stmt = $this->factory->namespace($namespace);
        foreach ($this->yaml['properties'] as $name => $details) {
            $stmt = $this->processProperty($class, $stmt, $name, $details);
        }

        ksort($this->uses);
        foreach ($this->uses as $useClass => $bool) {
            $stmt = $stmt
                ->addStmt($this->factory->use($useClass))
            ;
        }

        if (isset($this->yaml['annotations'])) {
            ksort($this->yaml['annotations']);
            foreach ($this->yaml['annotations'] as $annotation => $details) {
                $nestedResources = [];
                foreach ($details as $key => $value) {
                    $nestedResources[] = $key . '="' . $value . '"';
                }
                $this->docBlock[] = '@' . $annotation . '(' . implode(', ', $nestedResources) . ')';
            }
        }

        if (count($this->docBlock) > 0) {
            $class->setDocComment("/**\r\n * " . implode("\r\n * ", $this->docBlock) . "\r\n */");
        }

        return $stmt->addStmt($class)->getNode();
    }

    protected function processProperty($class, $stmt, $name, $details)
    {
        if (is_string($details)) {
            if ($this->exists($details)) {
                $this->uses[$details] = true;
            }

            $class->addStmt($this->createProperty($details, $name, $details));
            $methodName = Inflector::camelize($name);
            $class->addStmt($this->createMethod($details, $name, $methodName, $details));
            return $stmt;
        }

        if ($this->exists($details['type'])) {
            $this->uses[$details['type']] = true;
        }
        if (isset($details['wrap']) && $this->exists($details['wrap'])) {
            $this->uses[$details['wrap']] = true;
        }

        $class->addStmt($this->createProperty($details['type'], $name, $details));
        if (isset($details['wrap'])) {
            $class->addStmt($this->createProperty($details['wrap'], $name . '_wrapped', $details));
        }

        $methodName = Inflector::camelize($name);
        if (isset($details['method'])) {
            $methodName = $details['method'];
        }
        $class->addStmt($this->createMethod($details['type'], $name, $methodName, $details));

        return $stmt;
    }

    /**
     * @return Node
     */
    public function old(): Node
    {
        $factory = new BuilderFactory;

        $class = $factory->class($this->yaml['class'])
            ->implement($this->yaml['class'] . 'Interface')
            ->makeAbstract();

        $docBlock = [];

        if (isset($this->yaml['collection'])) {
            $nestedResources = [];
            foreach ($this->yaml['collection'] as $key => $resource) {
                $nestedResources[] = $key . '="' . $resource . '"';
            }
            $docBlock[] = '@Collection(' . implode(', ', $nestedResources) . ')';
        }

        if (isset($this->yaml['nested'])) {
            $nestedResources = [];
            foreach ($this->yaml['nested'] as $key => $resource) {
                $nestedResources[] = $key . '="' . $resource . '"';
            }
            $docBlock[] = '@Nested(' . implode(', ', $nestedResources) . ')';
        }

        if (isset($this->yaml['rename'])) {
            $nestedResources = [];
            foreach ($this->yaml['rename'] as $from => $to) {
                $nestedResources[] = $to . '="' . $from . '"';
            }
            $docBlock[] = '@Rename(' . implode(', ', $nestedResources) . ')';
        }

        if (count($docBlock) > 0) {
            $class->setDocComment("/**\r\n * " . implode("\r\n * ", $docBlock) . "\r\n */");
        }

        $class->addStmt(
            new Node\Stmt\TraitUse([
                new Node\Name('TransportAwareTrait')
            ])
        );

        foreach ($this->yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }

            $class->addStmt($this->createProperty($factory, $type, $name, $details));

            $wrappingClass = null;
            if (isset($this->yaml['wrap']) && isset($yaml['wrap'][$name])) {
                $wrappingClass = $this->yaml['wrap'][$name];
                $class->addStmt($this->createProperty($factory, $wrappingClass, $name . '_wrapped', $details));
            }

            $methodName = Inflector::camelize($name);
            if (isset($this->yaml['method'][$name])) {
                $methodName = $this->yaml['method'][$name];
            }
            $class->addStmt($this->createMethod($factory, $type, $name, $methodName, $details, $wrappingClass));
        }

        $stmt = $factory->namespace($yaml['namespace']);

        $addUses = [];
        if (isset($this->yaml['collection'])) {
            $addUses[Collection::class] = true;
        }
        if (isset($this->yaml['nested'])) {
            $addUses[Nested::class] = true;
        }
        if (isset($this->yaml['rename'])) {
            $addUses[Rename::class] = true;
        }

        $addUses['ApiClients\Foundation\Resource\TransportAwareTrait'] = true;

        if (isset($this->yaml['wrap'])) {
            foreach ($this->yaml['wrap'] as $name => $wrappingClass) {
                if (!class_exists($wrappingClass) && !interface_exists($wrappingClass)) {
                    continue;
                }

                if (isset($addUses[$wrappingClass])) {
                    continue;
                }

                $addUses[$wrappingClass] = true;
            }
        }
        foreach ($this->yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }

            if (!class_exists($type) && !interface_exists($type)) {
                continue;
            }

            if (isset($addUses[$type])) {
                continue;
            }

            $addUses[$type] = true;
        }

        ksort($addUses);

        foreach ($addUses as $useClass => $bool) {
            $stmt = $stmt
                ->addStmt($factory->use($useClass))
            ;
        }

        return $stmt->addStmt($class)->getNode();
    }

    protected function createProperty(string $type, string $name, $details): Property
    {
        $property = $this->factory->property($name)
            ->makeProtected()
            ->setDocComment("/**\r\n * @var " . $type . "\r\n */")
        ;
        if (isset($details['default'])) {
            $property->setDefault($details['default']);
        }

        return $property;
    }

    protected function createMethod(
        string $type,
        string $name,
        string $methodName,
        $details
    ): Method {
        $stmts = [
            new Node\Stmt\Return_(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $name
                )
            )
        ];

        if (isset($details['wrap'])) {
            $stmts = [];
            $stmts[] = new Node\Stmt\If_(
                new Node\Expr\Instanceof_(
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        $name . '_wrapped'
                    ),
                    new Node\Name($details['wrap'])
                ),
                [
                    'stmts' => [
                        new Node\Stmt\Return_(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                $name . '_wrapped'
                            )
                        ),
                    ],
                ]
            );
            $stmts[] = new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $name . '_wrapped'
                ),
                new Node\Expr\New_(
                    new Node\Name($details['wrap']),
                    [
                        new Node\Expr\PropertyFetch(
                            new Node\Expr\Variable('this'),
                            $name
                        ),
                    ]
                )
            );
            $stmts[] = new Node\Stmt\Return_(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    $name . '_wrapped'
                )
            );
        }

        return $this->factory->method($methodName)
            ->makePublic()
            ->setReturnType($type)
            ->setDocComment('/**
                              * @return ' . $type . '
                              */')
            ->addStmts($stmts);
    }

    protected function exists(string $ic): bool
    {
        if (class_exists($ic)) {
            return true;
        }

        if (interface_exists($ic)) {
            return true;
        }

        return false;
    }
}