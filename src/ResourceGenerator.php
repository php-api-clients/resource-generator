<?php declare(strict_types=1);

namespace ApiClients\Tools\ResourceGenerator;

use Exception;
use PhpParser\Node;
use PhpParser\PrettyPrinter;

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
     * @param  array     $file
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
     * @param  Node   $node
     * @return string
     */
    protected function printCode(Node $node): string
    {
        $prettyPrinter = new PrettyPrinter\Standard();

        return $prettyPrinter->prettyPrintFile([
            $node,
        ]) . PHP_EOL;
    }

    /**
     * @param  string    $fileName
     * @param  string    $fileContents
     * @throws Exception
     * @return bool
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

        $command = 'vendor/bin/php-cs-fixer fix ' .
            $fileName .
            ' --config=' .
            dirname(__DIR__) .
            DIRECTORY_SEPARATOR .
            '.php_cs ' .
            ' --allow-risky=yes -q -v --stop-on-violation --using-cache=no';

        system($command);
    }

    private function out(string $message)
    {
        $out = $this->out;
        $out($message);
    }
}
