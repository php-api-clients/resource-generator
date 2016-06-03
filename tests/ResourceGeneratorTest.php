<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport;

use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;
use Phake;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WyriHaximus\ApiClient\Tools\ResourceGenerator;

class ResourceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $temporaryDirectory;

    public function testConstruct()
    {
        $climate = Phake::mock(CLImate::class);
        $climate->arguments = Phake::mock(Manager::class);
        new ResourceGenerator($climate);
        Phake::verify($climate->arguments)->add($this->isType('array'));
    }

    public function testOutput()
    {
        $yamlPath = __DIR__ . DIRECTORY_SEPARATOR . 'yaml' . DIRECTORY_SEPARATOR;
        $resourcesPath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
        $definition = $yamlPath . 'project.yaml';
        $climate = Phake::mock(CLImate::class);
        $climate->arguments = Phake::mock(Manager::class);
        Phake::when($climate->arguments)->get('definition')->thenReturn($definition);
        Phake::when($climate->arguments)->get('path')->thenReturn($this->temporaryDirectory);
        (new ResourceGenerator($climate))->run();
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->temporaryDirectory), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (!is_file($name)) {
                continue;
            }

            $objectPath = substr($name, strlen($this->temporaryDirectory));

            $this->assertSame(
                file_get_contents($resourcesPath . $objectPath),
                file_get_contents($this->temporaryDirectory . $objectPath),
                $objectPath
            );
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('wyrihaximus-php-api-client-resource-generator-', true) . DIRECTORY_SEPARATOR;
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->rmdir($this->temporaryDirectory);
    }

    protected function rmdir($dir)
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
