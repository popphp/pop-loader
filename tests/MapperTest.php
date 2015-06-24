<?php

namespace Pop\Loader\Test;

use Pop\Loader\ClassMapper;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $mapper = new ClassMapper(__DIR__ . '/src');
        $this->assertInstanceOf('Pop\Loader\ClassMapper', $mapper);
        $this->assertTrue(isset($mapper->getClassMap()['Foo_Bar']));
        $this->assertTrue(isset($mapper->getClassMap()['MyApp\MyClass']));
    }

    public function testConstructorDirDoesNotExistException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $mapper = new ClassMapper(__DIR__ . '/bad');
    }

    public function testWriteToFile()
    {
        $mapper = new ClassMapper(__DIR__ . '/src');
        $mapper->writeToFile(__DIR__ . '/classmap.php');
        $this->assertFileExists(__DIR__ . '/classmap.php');
        unlink(__DIR__ . '/classmap.php');
    }

}
