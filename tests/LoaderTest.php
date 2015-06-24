<?php

namespace Pop\Loader\Test;

use Pop\Loader\ClassLoader;
use Pop\Loader\ClassMapper;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $autoloader = new ClassLoader();
        $this->assertInstanceOf('Pop\Loader\ClassLoader', $autoloader);
    }

    /**
     * @runInSeparateProcess
     */
    public function testLoadFromClassMap()
    {
        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromDir(__DIR__ . '/src');
        $app = new \MyApp\MyClass();
        $this->assertInstanceOf('MyApp\MyClass', $app);
    }

    public function testAddPrepend()
    {
        $autoloader = new ClassLoader();
        $autoloader->addPsr0('Foo', __DIR__ . '/src', true);
        $foo = new \Foo_Bar();
        $this->assertInstanceOf('Foo_Bar', $foo);
        $this->assertEquals('Foo', $autoloader->getPrefixesPsr0()[0]);
        $autoloader->unregister();
    }

    public function testAddPsr0()
    {
        $autoloader = new ClassLoader();
        $autoloader->addPsr0('Foo', __DIR__ . '/src');
        $foo = new \Foo_Bar();
        $this->assertInstanceOf('Foo_Bar', $foo);
        $this->assertEquals('Foo', $autoloader->getPrefixesPsr0()[0]);
    }

    public function testSet()
    {
        $autoloader = new ClassLoader();
        $autoloader->set('Foo', __DIR__ . '/src');
        $foo = new \Foo_Bar();
        $this->assertInstanceOf('Foo_Bar', $foo);
        $this->assertEquals('Foo', $autoloader->getPrefixesPsr0()[0]);
    }

    public function testSetPsr0()
    {
        $autoloader = new ClassLoader();
        $autoloader->setPsr0('Foo', __DIR__ . '/src');
        $foo = new \Foo_Bar();
        $this->assertInstanceOf('Foo_Bar', $foo);
        $this->assertEquals('Foo', $autoloader->getPrefixesPsr0()[0]);
    }

    public function testAddPsr4()
    {
        $autoloader = new ClassLoader();
        $autoloader->addPsr4('MyApp\\', __DIR__ . '/src');
        $app = new \MyApp\MyClass();
        $this->assertInstanceOf('MyApp\MyClass', $app);
        $this->assertEquals('MyApp\\', $autoloader->getPrefixesPsr4()[1]);
    }

    public function testAddPsr4Prepend()
    {
        $autoloader = new ClassLoader();
        $autoloader->addPsr4('MyApp\\', __DIR__ . '/src', true);
        $app = new \MyApp\MyClass();
        $this->assertInstanceOf('MyApp\MyClass', $app);
        $this->assertEquals('MyApp\\', $autoloader->getPrefixesPsr4()[0]);
    }

    public function testSetPsr4()
    {
        $autoloader = new ClassLoader();
        $autoloader->setPsr4('MyApp\\', __DIR__ . '/src');
        $app = new \MyApp\MyClass();
        $this->assertInstanceOf('MyApp\MyClass', $app);
        $this->assertEquals('MyApp\\', $autoloader->getPrefixesPsr4()[1]);
    }

    public function testAddDirDoesNotExistException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $autoloader = new ClassLoader();
        $autoloader->add('Foo', __DIR__ . '/bad');
    }

    public function testAddPsr4DirDoesNotExistException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $autoloader = new ClassLoader();
        $autoloader->setPsr4('MyApp\\', __DIR__ . '/bad');
    }

    public function testAddPsr4NoNamespaceSeparatorException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $autoloader = new ClassLoader();
        $autoloader->setPsr4('MyApp', __DIR__ . '/src');
    }

    public function testAddClassMap()
    {
        $autoloader = new ClassLoader();
        $autoloader->addClassMap(new ClassMapper(__DIR__ . '/src'));
        $this->assertTrue(isset($autoloader->getClassMap()['Foo_Bar']));
        $this->assertTrue(isset($autoloader->getClassMap()['MyApp\MyClass']));
    }

    public function testAddClassMapInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $autoloader = new ClassLoader();
        $autoloader->addClassMap('bad');
    }

    public function testAddClassMapFromFile()
    {
        $mapper = new ClassMapper(__DIR__ . '/src');
        $mapper->writeToFile(__DIR__ . '/classmap.php');

        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromFile(__DIR__ . '/classmap.php');
        $this->assertTrue(isset($autoloader->getClassMap()['Foo_Bar']));
        $this->assertTrue(isset($autoloader->getClassMap()['MyApp\MyClass']));

        unlink(__DIR__ . '/classmap.php');
    }

    public function testAddClassMapFromFileDoesNotExistException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromFile(__DIR__ . '/bad.php');
    }

    public function testAddClassMapFromDir()
    {
        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromDir(__DIR__ . '/src');
        $autoloader->setClassMapAuthoritative(true);
        $this->assertTrue($autoloader->isClassMapAuthoritative());
        $this->assertTrue(isset($autoloader->getClassMap()['Foo_Bar']));
        $this->assertTrue(isset($autoloader->getClassMap()['MyApp\MyClass']));
    }

    public function testAddClassMapFromDirDoesNotExistException()
    {
        $this->setExpectedException('Pop\Loader\Exception');
        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromDir(__DIR__ . '/bad');
    }


}
