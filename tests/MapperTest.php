<?php

namespace Pop\Loader\Test;

use Pop\Loader\ClassMapper;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{

    public function testConstructor()
    {
        $mapper = new ClassMapper(__DIR__ . '/src');
        $this->assertInstanceOf('Pop\Loader\ClassMapper', $mapper);
        $this->assertTrue(isset($mapper->getClassMap()['Foo_Bar']));
        $this->assertTrue(isset($mapper->getClassMap()['MyApp\MyClass']));
        $this->assertEquals(1, count($mapper->getSources()));
        $this->assertTrue($mapper->hasSource(__DIR__ . '/src'));
        $mapper->clearSources();
        $this->assertFalse($mapper->hasSource(__DIR__ . '/src'));
    }

    public function testConstructorDirDoesNotExistException()
    {
        $this->expectException('Pop\Loader\Exception');
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
