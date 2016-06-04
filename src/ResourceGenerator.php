<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Tools;

use Doctrine\Common\Inflector\Inflector;
use Exception;
use League\CLImate\CLImate;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use Symfony\Component\Yaml\Yaml;

class ResourceGenerator
{
    protected $climate;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;

        $this->setUpArguments();
    }

    protected function setUpArguments()
    {
        $this->climate->arguments->add([
            'definition' => [
                'description' => 'YAML definition file',
                'required'    => true,
            ],
            'path' => [
                'description' => 'Path to the resource directory',
                'required'    => true,
            ],
            'sync' => [
                'prefix'       => 's',
                'longPrefix'   => 'sync',
                'defaultValue' => true,
                'noValue'      => false,
                'description'  => 'Don\'t generate Sync resource',
                'castTo'       => 'bool',
            ],
            'async' => [
                'prefix'       => 'as',
                'longPrefix'   => 'async',
                'defaultValue' => true,
                'noValue'      => false,
                'description'  => 'Don\'t generate Async resource',
                'castTo'       => 'bool',
            ],
        ]);
    }

    public function run()
    {
        $yaml = $this->readYaml($this->climate->arguments->get('definition'));
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createBaseClass($yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                'Interface.php',
            $this->createInterface($yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                'Async' .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createExtendingClass('Async', $yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                'Sync' .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createExtendingClass('Sync', $yaml)
        );
    }

    protected function readYaml(string $filename): array
    {
        return Yaml::parse(file_get_contents($filename));
    }

    protected function createBaseClass(array $yaml)
    {
        $factory = new BuilderFactory;

        $class = $factory->class($yaml['class'])
            ->implement($yaml['class'] . 'Interface')
            ->makeAbstract();
        $class->addStmt(
            new Node\Stmt\TraitUse([
                new Node\Name('TransportAwareTrait')
            ])
        );

        foreach ($yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }
            $class->addStmt($this->createProperty($factory, $type, $name, $details));
            $class->addStmt($this->createMethod($factory, $type, $name, $details));
        }

        $node = $factory->namespace($yaml['namespace'])
            ->addStmt($factory->use('WyriHaximus\ApiClient\Resource\TransportAwareTrait'))
            ->addStmt($class)

            ->getNode()
        ;

        $prettyPrinter = new PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile([
            $node
        ]) . PHP_EOL;
    }

    protected function createInterface(array $yaml)
    {
        $factory = new BuilderFactory;

        $class = $factory->interface($yaml['class'] . 'Interface')
            ->extend('ResourceInterface');

        foreach ($yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }
            $class->addStmt($this->createMethod($factory, $type, $name, $details));
        }

        $node = $factory->namespace($yaml['namespace'])
            ->addStmt($factory->use('WyriHaximus\ApiClient\Resource\ResourceInterface'))
            ->addStmt($class)
            ->getNode()
        ;

        $prettyPrinter = new PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile([
            $node
        ]) . PHP_EOL;
    }

    protected function createProperty(BuilderFactory $factory, string $type, string $name, $details): Property
    {
        $property = $factory->property($name)
            ->makeProtected()
            ->setDocComment('/**
                              * @var ' . $type . '
                              */');
        if (isset($details['default'])) {
            $property->setDefault($details['default']);
        }

        return $property;
    }

    protected function createMethod(BuilderFactory $factory, string $type, string $name, $details): Method
    {
        return $factory->method(Inflector::camelize($name))
            ->makePublic()
            ->setReturnType($type)
            ->setDocComment('/**
                              * @return ' . $type . '
                              */')
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        $name
                    )
                )
            );
    }

    protected function createExtendingClass(string $type, array $yaml)
    {
        $factory = new BuilderFactory;

        $class = $factory->class($yaml['class'])
            ->extend('Base' . $yaml['class']);

        $class->addStmt($factory->method('refresh')
            ->makePublic()
            ->setReturnType($yaml['class'])
            ->addStmt(
                new Node\Stmt\Return_(
                    new Node\Expr\MethodCall(
                        new Node\Expr\Variable('this'),
                        'wait',
                        [
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('this'),
                                'callAsync',
                                [
                                    new Node\Scalar\String_('refresh'),
                                ]
                            ),
                        ]
                    )
                )
            ));

        $node = $factory->namespace($yaml['namespace'] . '\\' . $type)
            ->addStmt($factory->use($yaml['namespace'] . '\\' . $yaml['class'])->as('Base' . $yaml['class']))
            ->addStmt($class)

            ->getNode()
        ;

        $prettyPrinter = new PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile([
            $node
        ]) . PHP_EOL;
    }

    protected function save(string $directory, string $fileName, string $fileContents)
    {
        if (file_exists($directory . $fileName)) {
            return;
        }

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        if (!file_exists($directory)) {
            throw new Exception('Unable to create: ' . $directory);
        }

        file_put_contents($directory . $fileName, $fileContents);
    }
}
