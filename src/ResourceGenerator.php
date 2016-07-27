<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Tools;

use Aura\Cli\Context;
use Aura\Cli\Stdio;
use Doctrine\Common\Inflector\Inflector;
use Exception;
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
use WyriHaximus\ApiClient\Annotations\Collection;
use WyriHaximus\ApiClient\Annotations\Nested;
use WyriHaximus\ApiClient\Annotations\Rename;
use WyriHaximus\ApiClient\Resource\ResourceInterface;

class ResourceGenerator
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Stdio
     */
    protected $stdio;

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Fixer
     */
    protected $fixer;

    /**
     * @var array
     */
    protected $fixers;

    public function __construct(Context $context, Stdio $stdio)
    {
        $this->context = $context;
        $this->stdio = $stdio;

        $this->setUpArguments();
        $this->setUpFixers();
    }

    protected function setUpArguments()
    {
        $getOpt = $this->context->getopt([]);
        $i = 0;
        do {
            $i++;
            $opt = $getOpt->get($i);
            if ($opt === null) {
                break;
            }
            $this->definitions[] = $opt;
        } while (true);
        $this->path = array_pop($this->definitions);
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
        $this->checkValidity();

        foreach ($this->definitions as $definition) {
            $this->stdio->outln('-----');
            $this->stdio->outln('- Definition: ' . $definition);
            $this->stdio->outln('-----');
            $this->generateFromDefinition($definition);
            $this->stdio->outln('-----');
        }
    }

    public function checkValidity()
    {
        if (count($this->definitions) < 1) {
            throw new \InvalidArgumentException('Not enough arguments');
        }

        if ($this->path === null) {
            throw new \InvalidArgumentException('No path set');
        }

        if (!file_exists($this->path)) {
            throw new \InvalidArgumentException('Path "' . $this->path . '" doesn\'t exist');
        }

        if (!is_dir($this->path)) {
            throw new \InvalidArgumentException('Path "' . $this->path . '" isn\'t a directory');
        }

        foreach ($this->definitions as $definition) {
            if (!file_exists($definition)) {
                throw new \InvalidArgumentException('Definition "' . $definition . '" doesn\'t exist');
            }
        }
    }

    public function generateFromDefinition(string $definition)
    {
        $yaml = $this->readYaml($definition);

        $namespacePadding = explode('\\', $yaml['class']);
        $namespace = explode('\\', $yaml['namespace']);

        $yaml['class'] = array_pop($namespacePadding);
        $yaml['namespace'] = implode('\\', array_merge($namespace, $namespacePadding));

        $namespacePathPadding = implode(DIRECTORY_SEPARATOR, $namespacePadding);
        $baseClass = implode(
            '\\',
            array_merge(
                $namespace,
                $namespacePadding,
                [
                    $yaml['class']
                ]
            )
        );


        if (isset($yaml['rename'])) {
            foreach ($yaml['rename'] as $key => $resource) {
                $yaml['properties'][$resource] = $yaml['properties'][$key];
                unset($yaml['properties'][$key]);
            }
        }


        $this->stdio->out('Interface: generating');
        $this->save(
            $this->path .
                DIRECTORY_SEPARATOR .
                $namespacePathPadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                'Interface.php',
            $this->createInterface($yaml)
        );

        $this->stdio->out('Base class: generating');
        $this->save(
            $this->path .
                DIRECTORY_SEPARATOR .
                $namespacePathPadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createBaseClass($yaml)
        );

        $this->stdio->out('Async class: generating');
        $this->save(
            $this->path .
                DIRECTORY_SEPARATOR .
                'Async' .
                DIRECTORY_SEPARATOR .
                $namespacePathPadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createExtendingClass(
                implode(
                    '\\',
                    array_merge(
                        $namespace,
                        [
                            'Async',
                        ],
                        $namespacePadding
                    )
                ),
                $yaml['class'],
                $baseClass
            )
        );

        $this->stdio->out('Sync class: generating');
        $this->save(
            $this->path .
                DIRECTORY_SEPARATOR .
                'Sync' .
                DIRECTORY_SEPARATOR .
                $namespacePathPadding .
                DIRECTORY_SEPARATOR,
            $yaml['class'] .
                '.php',
            $this->createExtendingClass(
                implode(
                    '\\',
                    array_merge(
                        $namespace,
                        [
                            'Sync',
                        ],
                        $namespacePadding
                    )
                ),
                $yaml['class'],
                $baseClass
            )
        );
    }

    protected function readYaml(string $filename): array
    {
        return Yaml::parse(file_get_contents($filename));
    }

    protected function createBaseClass(array $yaml): string
    {
        $factory = new BuilderFactory;

        $class = $factory->class($yaml['class'])
            ->implement($yaml['class'] . 'Interface')
            ->makeAbstract();

        $docBlock = [];

        if (isset($yaml['collection'])) {
            $nestedResources = [];
            foreach ($yaml['collection'] as $key => $resource) {
                $nestedResources[] = $key . '="' . $resource . '"';
            }
            $docBlock[] = '@Collection(' . implode(', ', $nestedResources) . ')';
        }

        if (isset($yaml['nested'])) {
            $nestedResources = [];
            foreach ($yaml['nested'] as $key => $resource) {
                $nestedResources[] = $key . '="' . $resource . '"';
            }
            $docBlock[] = '@Nested(' . implode(', ', $nestedResources) . ')';
        }

        if (isset($yaml['rename'])) {
            $nestedResources = [];
            foreach ($yaml['rename'] as $key => $resource) {
                $nestedResources[] = $resource . '="' . $key . '"';
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

        foreach ($yaml['properties'] as $name => $details) {
            $type = $details;
            if (is_array($details)) {
                $type = $details['type'];
            }
            $class->addStmt($this->createProperty($factory, $type, $name, $details));
            $class->addStmt($this->createMethod($factory, $type, $name, $details));
        }

        $stmt = $factory->namespace($yaml['namespace']);
        if (isset($yaml['collection'])) {
            $stmt = $stmt->addStmt(
                $factory->use(Collection::class)
            );
        }
        if (isset($yaml['nested'])) {
            $stmt = $stmt->addStmt(
                $factory->use(Nested::class)
            );
        }
        if (isset($yaml['rename'])) {
            $stmt = $stmt->addStmt(
                $factory->use(Rename::class)
            );
        }
        $stmt
            ->addStmt($factory->use('WyriHaximus\ApiClient\Resource\TransportAwareTrait'))
            ->addStmt($class)
        ;

        $node = $stmt->getNode();

        $prettyPrinter = new PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile([
            $node
        ]) . PHP_EOL;
    }

    protected function createInterface(array $yaml): string
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
            ->addStmt($factory->use(ResourceInterface::class))
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

    protected function createExtendingClass(string $namespace, string $className, string $baseClass): string
    {
        $factory = new BuilderFactory;

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
                                'callAsync',
                                [
                                    new Node\Scalar\String_('refresh'),
                                ]
                            ),
                        ]
                    )
                )
            ));

        $node = $factory->namespace($namespace)
            ->addStmt($factory->use($baseClass)->as('Base' . $className))
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
            $this->stdio->outln(', exists!');
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

        $this->stdio->out(', writing');
        file_put_contents($directory . $fileName, $fileContents);

        do {
            usleep(500);
        } while (!file_exists($directory . $fileName));

        $this->stdio->out(', applying PSR-2');
        $this->applyPsr2($directory . $fileName);
        $this->stdio->outln(', done!');
    }

    /**
     * @param string $fileName
     */
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

        file_put_contents(
            $fileName,
            str_replace(
                '<?php',
                '<?php declare(strict_types=1);',
                file_get_contents(
                    $fileName
                )
            )
        );
    }


    /**
     * @param ConfigInterface $config
     *
     * @return FixerInterface[]
     */
    private function prepareFixers(ConfigInterface $config): array
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
