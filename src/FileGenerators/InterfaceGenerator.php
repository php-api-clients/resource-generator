<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Resource\ResourceInterface;
use function ApiClients\Tools\ResourceGenerator\exists;
use ApiClients\Tools\ResourceGenerator\FileGeneratorInterface;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\BuilderFactory;
use PhpParser\Node;

final class InterfaceGenerator implements FileGeneratorInterface
{
    /**
     * @var array
     */
    protected $yaml;

    /**
     * @var array
     */
    protected $uses = [
        ResourceInterface::class => true,
    ];

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
            str_replace('\\', DIRECTORY_SEPARATOR, $this->yaml['class']) .
            'Interface.php'
        ;
    }

    /**
     * @return Node
     */
    public function generate(): Node
    {
        $classChunks = explode('\\', $this->yaml['class']);
        $baseClass = array_pop($classChunks);
        $className = $baseClass . 'Interface';
        $namespace = $this->yaml['src']['namespace'];
        if (count($classChunks) > 0) {
            $namespace .= '\\' . implode('\\', $classChunks);
            $namespace = str_replace('\\\\', '\\', $namespace);
        }

        $factory = new BuilderFactory();

        $class = $factory->interface($className)
            ->extend('ResourceInterface');

        $class->addStmt(
            new Node\Stmt\ClassConst(
                [
                    new Node\Const_(
                        'HYDRATE_CLASS',
                        new Node\Scalar\String_(
                            $this->yaml['class']
                        )
                    ),
                ]
            )
        );

        foreach ($this->yaml['properties'] as $name => $details) {
            if (is_array($details)) {
                $types = $details['type'];
            } else {
                $types = $details;
            }

            $types = explode('|', $types);
            foreach ($types as $type) {
                if (exists($type)) {
                    $this->uses[$type] = true;
                }
            }

            $methodName = Inflector::camelize($name);
            if (is_array($details) && isset($details['method'])) {
                $methodName = $details['method'];
            }
            $method = $factory->method($methodName)
                ->makePublic()
                ->setDocComment(
                    "/**\r\n * @return " . implode('|', $types) . "\r\n */"
                );
            if (count($types) === 1) {
                $method = $method->setReturnType($types[0]);
            }
            $class->addStmt($method);
        }

        $stmt = $factory->namespace($namespace);

        ksort($this->uses);
        foreach ($this->uses as $useClass => $bool) {
            $stmt = $stmt
                ->addStmt($factory->use($useClass))
            ;
        }

        return $stmt->addStmt($class)
            ->getNode()
        ;
    }
}
