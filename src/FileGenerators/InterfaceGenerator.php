<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator\FileGenerators;

use ApiClients\Foundation\Resource\ResourceInterface;
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
        $factory = new BuilderFactory();

        $class = $factory->interface($this->yaml['class'] . 'Interface')
            ->extend('ResourceInterface');

        foreach ($this->yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }
            $methodName = Inflector::camelize($name);
            if (isset($this->yaml['method'][$name])) {
                $methodName = $this->yaml['method'][$name];
            }
            $class->addStmt($factory->method($methodName)
                ->makePublic()
                ->setReturnType($type)
                ->setDocComment(
                    "/**\r\n * @return " . $type . "\r\n */"
                )
            );
        }

        return $factory->namespace($this->yaml['src']['namespace'])
            ->addStmt($factory->use(ResourceInterface::class))
            ->addStmt($class)
            ->getNode()
        ;
    }
}
