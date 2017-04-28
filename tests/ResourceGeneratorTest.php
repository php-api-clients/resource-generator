<?php declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport;

use Phake;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ApiClients\Tools\ResourceGenerator\ResourceGenerator;
use function ApiClients\Tools\ResourceGenerator\readYaml;
use function ApiClients\Tools\ResourceGenerator\readYamlDir;

class ResourceGeneratorTest extends TestCase
{
    /**
     * @var string
     */
    protected $temporaryDirectory;

    public function testResourceGenerator()
    {
        $yamlPath = __DIR__ . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
        $resourcesPath = __DIR__ . DIRECTORY_SEPARATOR . 'expected-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        $resourcesPathTests = __DIR__ . DIRECTORY_SEPARATOR . 'expected-app' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR;

        $configuration = [];
        $configuration += readYaml(__DIR__ . DIRECTORY_SEPARATOR . '../generator-settings.yml');
        $configuration += readYaml(__DIR__ . DIRECTORY_SEPARATOR . 'initial-app/resources.yml');
        $configuration['files'] = readYamlDir(__DIR__ . DIRECTORY_SEPARATOR . 'initial-app' . DIRECTORY_SEPARATOR . $configuration['yaml_location']);
        $configuration['root'] = $this->temporaryDirectory;

        (new ResourceGenerator($configuration, function () {}))->run();

        foreach ([
             $resourcesPath => $this->temporaryDirectory . 'src' . DIRECTORY_SEPARATOR,
             $resourcesPathTests => $this->temporaryDirectory . 'tests' . DIRECTORY_SEPARATOR,
         ] as $from => $to) {
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($objects as $name => $object) {
                if (!is_file($name)) {
                    continue;
                }

                $objectPath = substr($name, strlen($from));

                $this->assertFileExists($to . $objectPath);

                $expected = file_get_contents($from . $objectPath);
                $actual = file_get_contents($to . $objectPath);

                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $expected = str_replace(
                        [
                            "\r",
                            "\n",
                        ],
                        '',
                        $expected
                    );
                    $actual = str_replace(
                        [
                            "\r",
                            "\n",
                        ],
                        '',
                        $actual
                    );
                }

                $this->assertSame(
                    $expected,
                    $actual,
                    $objectPath
                );
            }
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('wyrihaximus-php-api-client-resource-generator-', true) . DIRECTORY_SEPARATOR;
        mkdir($this->temporaryDirectory);
        mkdir($this->temporaryDirectory . 'src');
        mkdir($this->temporaryDirectory . 'tests');
    }

    public function tearDown()
    {
        parent::tearDown();
        //$this->rmdir($this->temporaryDirectory);
    }

    protected function rmdir(string $dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $directory = dir($dir);
        while (false !== ($entry = $directory->read())) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (is_dir($dir . $entry)) {
                $this->rmdir($dir . $entry . DIRECTORY_SEPARATOR);
                continue;
            }

            if (is_file($dir . $entry)) {
                unlink($dir . $entry);
                continue;
            }
        }
        $directory->close();
        rmdir($dir);
    }
}
