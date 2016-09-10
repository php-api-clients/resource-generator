<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Resource\EmptyResourceInterface;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use function ApiClients\Tools\ResourceGenerator\exists;

final class EmptyBaseClassGenerator implements FileGeneratorInterface
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
        EmptyResourceInterface::class => true,
    ];

    /**
     * InterfaceGenerator constructor.
     * @param array $yaml
     */
    public function __construct(array $yaml)
    {
        $this->yaml = $yaml;
        $this->factory = new BuilderFactory();
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
        $className = array_pop($classChunks);
        $namespace = $this->yaml['src']['namespace'];
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        $class = $this->factory->class('Empty' . $className)
            ->implement($className . 'Interface')
            ->implement('EmptyResourceInterface')
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

        return $stmt->addStmt($class)->getNode();
    }

    /**
     * @param \PhpParser\Builder\Class_ $class
     */
    protected function processProperty($class, $stmt, $name, $details)
    {
        if (is_string($details)) {
            if (exists($details)) {
                $this->uses[$details] = true;
            }

            $methodName = Inflector::camelize($name);
            $class->addStmt($this->createMethod($details, $name, $methodName, $details));

            return $stmt;
        }

        if (exists($details['type'])) {
            $this->uses[$details['type']] = true;
        }

        $methodName = Inflector::camelize($name);
        if (isset($details['method'])) {
            $methodName = $details['method'];
        }
        $class->addStmt($this->createMethod($details['type'], $name, $methodName, $details));

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
        string $type,
        string $name,
        string $methodName,
        $details
    ): Method {
        $stmts = [
            new Node\Stmt\Return_(
                new Node\Expr\ConstFetch(
                    new Node\Name('null')
                )
            )
        ];

        return $this->factory->method($methodName)
            ->makePublic()
            ->setReturnType($type)
            ->setDocComment('/**
                              * @return ' . $type . '
                              */')
            ->addStmts($stmts);
    }
}
