<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport;

use Aura\Cli\Context;
use Aura\Cli\Stdio;
use Phake;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WyriHaximus\ApiClient\Tools\ResourceGenerator;

class ResourceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $temporaryDirectory;

    public function testConstruct()
    {
        $context = Phake::mock(Context::class);
        $stdio = Phake::mock(Stdio::class);
        $getopt = Phake::mock(Context\Getopt::class);
        Phake::when($getopt)->get(1)->thenReturn('project.yaml');
        Phake::when($getopt)->get(2)->thenReturn('project-build.yaml');
        Phake::when($getopt)->get(3)->thenReturn('./');
        Phake::when($getopt)->get(4)->thenReturn(null);
        Phake::when($context)->getopt([])->thenReturn($getopt);
        new ResourceGenerator($context, $stdio);
        Phake::verify($getopt, Phake::never())->get(0);
        Phake::verify($getopt)->get(1);
        Phake::verify($getopt)->get(2);
        Phake::verify($getopt)->get(3);
        Phake::verify($getopt)->get(4);
        Phake::verify($context)->getopt([]);
    }

    public function testOutput()
    {
        $yamlPath = __DIR__ . DIRECTORY_SEPARATOR . 'yaml' . DIRECTORY_SEPARATOR;
        $resourcesPath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
        $context = Phake::mock(Context::class);
        $stdio = Phake::mock(Stdio::class);
        $getopt = Phake::mock(Context\Getopt::class);
        Phake::when($getopt)->get(1)->thenReturn($yamlPath . 'project.yaml');
        Phake::when($getopt)->get(2)->thenReturn($yamlPath . 'project-build.yaml');
        Phake::when($getopt)->get(3)->thenReturn($this->temporaryDirectory);
        Phake::when($getopt)->get(4)->thenReturn(null);
        Phake::when($context)->getopt([])->thenReturn($getopt);
        (new ResourceGenerator($context, $stdio))->run();
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourcesPath), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (!is_file($name)) {
                continue;
            }

            $objectPath = substr($name, strlen($resourcesPath));

            $this->assertFileExists($this->temporaryDirectory . $objectPath);
            $this->assertTrue(
                file_get_contents($resourcesPath . $objectPath) ==
                file_get_contents($this->temporaryDirectory . $objectPath),
                $objectPath
            );
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('wyrihaximus-php-api-client-resource-generator-', true) . DIRECTORY_SEPARATOR;
        mkdir($this->temporaryDirectory);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->rmdir($this->temporaryDirectory);
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
