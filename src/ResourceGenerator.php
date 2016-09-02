<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator;

use Exception;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use Symfony\CS\Config\Config;
use Symfony\CS\ConfigAwareInterface;
use Symfony\CS\ConfigInterface;
use Symfony\CS\Fixer;
use Symfony\CS\FixerInterface;

class ResourceGenerator
{
    /**
     * @var callable
     */
    protected $out = 'ApiClients\Tools\ResourceGenerator\outln';

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var FileGeneratorInterface[]
     */
    protected $generators = [];

    /**
     * @var string
     */
    protected $pathSrc;

    /**
     * @var string
     */
    protected $pathTests;

    /**
     * @var Fixer
     */
    protected $fixer;

    /**
     * @var array
     */
    protected $fixers;

    public function __construct(array $configuration, callable $out = null)
    {
        $this->configuration = $configuration;
        $this->generators = $this->configuration['file_generators'];

        if (is_callable($out)) {
            $this->out = $out;
        }

        $this->setUpFixers();
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
        foreach ($this->configuration['files'] as $definition) {
            $this->out('-----');
            $this->out('- Definition: ' . $definition['class']);
            $definition = $this->applyAnnotationsToDefinition($definition);
            $this->generateFromDefinition($definition);
            $this->out('-----');
        }
    }

    protected function applyAnnotationsToDefinition(array $definition): array
    {
        foreach ($definition['properties'] as $property => $properties) {
            if (!isset($properties['annotations'])) {
                continue;
            }

            foreach ($properties['annotations'] as $annotation => $input) {
                if (!isset($this->configuration['annotation_handlers'][$annotation])) {
                    continue;
                }

                if (!is_subclass_of(
                    $this->configuration['annotation_handlers'][$annotation],
                    AnnotationHandlerInterface::class
                )) {
                    continue;
                }

                $definition = forward_static_call_array(
                    [
                        $this->configuration['annotation_handlers'][$annotation],
                        'handle',
                    ],
                    [
                        $property,
                        $definition,
                        $input,
                    ]
                );
            }
        }

        return $definition;
    }

    /**
     * @param array $file
     * @throws Exception
     */
    protected function generateFromDefinition(array $file)
    {
        $config = $this->configuration + $file;
        unset($config['files']);

        foreach ($this->generators as $generatorClass) {
            /** @var FileGeneratorInterface $generator */
            $generator = new $generatorClass($config);
            $fileName = $generator->getFilename();
            $this->out('----');
            $this->out('-- Generator: ' . $generatorClass);
            $this->out('-- File: ' . $fileName);
            $this->out('---');
            $this->out('-- Generating');
            $node = $generator->generate();
            $this->out('-- Printing');
            $code = $this->printCode($node);
            $this->out('-- Saving file');
            $continue = $this->save($fileName, $code);
            if (!$continue) {
                continue;
            }
            $this->out('-- Applying code standards');
            $this->applyPsr2($fileName);
            $this->out('----');
        }
    }

    /**
     * @param Node $node
     * @return string
     */
    protected function printCode(Node $node): string
    {
        $prettyPrinter = new PrettyPrinter\Standard();
        return $prettyPrinter->prettyPrintFile([
            $node
        ]) . PHP_EOL;
    }

    /**
     * @param string $fileName
     * @param string $fileContents
     * @return bool
     * @throws Exception
     */
    protected function save(string $fileName, string $fileContents)
    {
        $fileName = $this->configuration['root'] . $fileName;

        if (file_exists($fileName)) {
            $this->out('-- Exists');
            return false;
        }

        $directory = dirname($fileName);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!file_exists($directory)) {
            throw new Exception('Unable to create: ' . $directory);
        }

        file_put_contents($fileName, $fileContents);

        do {
            usleep(500);
        } while (!file_exists($fileName));

        return true;
    }

    /**
     * @param string $fileName
     */
    protected function applyPsr2($fileName)
    {
        $fileName = $this->configuration['root'] . $fileName;

        $file = new \SplFileInfo($fileName);
        $new = file_get_contents($file->getRealpath());

        foreach ($this->fixers as $fixer) {
            if (!$fixer->supports($file)) {
                continue;
            }

            $new = $fixer->fix($file, $new);
        }

        file_put_contents(
            $fileName,
            str_replace(
                '<?php',
                '<?php declare(strict_types=1);',
                $new
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

    private function out(string $message)
    {
        $out = $this->out;
        $out($message);
    }
}
