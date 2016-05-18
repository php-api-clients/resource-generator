<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport;

use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;
use Phake;
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
}
