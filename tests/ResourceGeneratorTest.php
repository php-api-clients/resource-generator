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
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('wyrihaximus-php-api-client-resource-generator-', true) . DIRECTORY_SEPARATOR;
        $climate = Phake::mock(CLImate::class);
        $climate->arguments = Phake::mock(Manager::class);
        Phake::when($climate->arguments)->get('definition')->thenReturn($definition);
        Phake::when($climate->arguments)->get('path')->thenReturn($path);
        (new ResourceGenerator($climate))->run();
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (!is_file($name)) {
                continue;
            }

            $objectPath = substr($name, strlen($path));

            $this->assertSame(
                file_get_contents($resourcesPath . $objectPath),
                file_get_contents($path . $objectPath),
                $objectPath
            );
        }
    }
}
