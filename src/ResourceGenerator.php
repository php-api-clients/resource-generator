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
use Symfony\CS\Config\Config;
use Symfony\CS\ConfigAwareInterface;
use Symfony\CS\ConfigInterface;
use Symfony\CS\FileCacheManager;
use Symfony\CS\Fixer;
use Symfony\CS\FixerInterface;

class ResourceGenerator
{
    protected $climate;

    /**
     * @var Fixer
     */
    protected $fixer;

    /**
     * @var array
     */
    protected $fixers;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;

        $this->setUpArguments();
        $this->setUpFixers();
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

    protected function setUpFixers()
    {
        $this->fixer = new Fixer();
        $this->fixer->registerCustomFixers([
            new Fixer\Symfony\ExtraEmptyLinesFixer(),
            new Fixer\Symfony\SingleBlankLineBeforeNamespaceFixer(),
            new Fixer\PSR0\Psr0Fixer(),
            new Fixer\PSR1\EncodingFixer(),
            new Fixer\PSR1\ShortTagFixer(),
            new Fixer\PSR2\BracesFixer(),
            new Fixer\PSR2\ElseifFixer(),
            new Fixer\PSR2\EofEndingFixer(),
            new Fixer\PSR2\FunctionCallSpaceFixer(),
            new Fixer\PSR2\FunctionDeclarationFixer(),
            new Fixer\PSR2\IndentationFixer(),
            new Fixer\PSR2\LineAfterNamespaceFixer(),
            new Fixer\PSR2\LinefeedFixer(),
            new Fixer\PSR2\LowercaseConstantsFixer(),
            new Fixer\PSR2\LowercaseKeywordsFixer(),
            new Fixer\PSR2\MethodArgumentSpaceFixer(),
            new Fixer\PSR2\MultipleUseFixer(),
            new Fixer\PSR2\ParenthesisFixer(),
            new Fixer\PSR2\PhpClosingTagFixer(),
            new Fixer\PSR2\SingleLineAfterImportsFixer(),
            new Fixer\PSR2\TrailingSpacesFixer(),
            new Fixer\PSR2\VisibilityFixer(),
            new Fixer\Contrib\NewlineAfterOpenTagFixer(),
            new EmptyLineAboveDocblocksFixer(),
        ]);
        $config = Config::create()->
        fixers($this->fixer->getFixers())
        ;
        $this->fixer->addConfig($config);
        $this->fixers = $this->prepareFixers($config);
    }

    public function run()
    {
        $yaml = $this->readYaml($this->climate->arguments->get('definition'));

        $class = explode('\\', $yaml['class']);
        $namespace = explode('\\', $yaml['namespace']);

        $yaml['class'] = array_pop($class);
        $yaml['namespace'] = implode('\\', array_merge($namespace, $class));

        $classNamespacePadding = implode(DIRECTORY_SEPARATOR, $class);

        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                $classNamespacePadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createBaseClass($yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                $classNamespacePadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                'Interface.php',
            $this->createInterface($yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                'Async' .
                DIRECTORY_SEPARATOR .
                $classNamespacePadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createExtendingClass('Async', $yaml)
        );
        $this->save(
            $this->climate->arguments->get('path') .
                DIRECTORY_SEPARATOR .
                'Sync' .
                DIRECTORY_SEPARATOR .
                $classNamespacePadding .
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
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
        if (file_exists($directory . $fileName)) {
            return;
        }

        $path = $directory . $fileName;
        $pathChunks = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($pathChunks);
        $path = implode(DIRECTORY_SEPARATOR, $pathChunks);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path)) {
            throw new Exception('Unable to create: ' . $path);
        }

        file_put_contents($directory . $fileName, $fileContents);

        do {
            usleep(500);
        } while (!file_exists($directory . $fileName));

        $this->applyPsr2($directory . $fileName);
    }

    protected function applyPsr2($fileName)
    {
        $file = new \SplFileInfo($fileName);
        $this->fixer->fixFile(
            $file,
            $this->fixers,
            false,
            false,
            new FileCacheManager(
                false,
                '',
                $this->fixers
            )
        );
    }


    /**
     * @param ConfigInterface $config
     *
     * @return FixerInterface[]
     */
    private function prepareFixers(ConfigInterface $config)
    {
        $fixers = $config->getFixers();

        foreach ($fixers as $fixer) {
            if ($fixer instanceof ConfigAwareInterface) {
                $fixer->setConfig($config);
            }
        }

        return $fixers;
    }
}
