<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Hydrator\Annotations\EmptyResource;
use ApiClients\Foundation\Resource\AbstractResource;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use function ApiClients\Tools\ResourceGenerator\exists;

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
        EmptyResource::class => true,
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
                $this->docBlock[] = '@' .
                    $annotation .
                    "(\r\n *     " .
                    implode(",\r\n *     ", $nestedResources) .
                    "\r\n * )"
                ;
            }
        }

        $namespacePrefix = ltrim(implode('\\', $classChunks) . '\\', '\\');
        $this->docBlock[] = '@EmptyResource("' . $namespacePrefix . 'Empty' . $className . '")';

        if (count($this->docBlock) > 0) {
            $class->setDocComment("/**\r\n * " . implode("\r\n * ", $this->docBlock) . "\r\n */");
        }

        return $stmt->addStmt($class)->getNode();
    }

    /**
     * @param \PhpParser\Builder\Class_ $class
     */
    protected function processProperty($class, $stmt, $name, $details)
    {
        if (is_string($details)) {
            $types = explode('|', $details);
            foreach ($types as $type) {
                if (exists($type)) {
                    $this->uses[$type] = true;
                }
            }

            $class->addStmt($this->createProperty($details, $name, $details));
            $methodName = Inflector::camelize($name);
            $class->addStmt($this->createMethod($types, $name, $methodName, $details));
            return $stmt;
        }

        $types = explode('|', $details['type']);
        foreach ($types as $type) {
            if (exists($type)) {
                $this->uses[$type] = true;
            }
        }
        if (isset($details['wrap']) && exists($details['wrap'])) {
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
        $class->addStmt($this->createMethod($types, $name, $methodName, $details));

        return $stmt;
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
        array $types,
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

        $method = $this->factory->method($methodName)
            ->makePublic()
            ->setDocComment('/**
                              * @return ' . implode('|',$types) . '
                              */')
            ->addStmts($stmts);
        if (count($types) === 1) {
            $method = $method->setReturnType($types[0]);
        }
        return $method;
    }
}
