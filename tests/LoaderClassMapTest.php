<?php

namespace Pop\Loader\Test;

use Pop\Loader\ClassLoader;
use PHPUnit\Framework\TestCase;

class LoaderClassMapTest extends TestCase
{

    /**
     * @runInSeparateProcess
     */
    public function testLoadFromClassMap()
    {
        $autoloader = new ClassLoader();
        $autoloader->addClassMapFromDir(__DIR__ . '/src');
        $autoloader->setClassMapAuthoritative(true);
        $this->assertFalse($autoloader->loadClass('MyApp\NoClass'));
    }
}
