pop-loader
==========

[![Build Status](https://travis-ci.org/popphp/pop-loader.svg?branch=master)](https://travis-ci.org/popphp/pop-loader)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-loader)](http://cc.popphp.org/pop-loader/)

OVERVIEW
--------
`pop-loader` is a component for managing the autoloading of an application. If, for some reason you
do not or cannot use Composer, `pop-loader` provides an alternative with similar features and API.
It supports both PSR-4 and PSR-0 autoloading standards. Additionally, there is support for generating
and loading class maps, if you are interested in boosting the speed and performance of your
application's load times.

`pop-loader` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Download or clone this repository and follow the examples below to wire up the autoloading
required by your application. Or, you can install `pop-loader` using Composer - ironic, I know :)

    composer require popphp/pop-loader

BASIC USAGE
-----------

### Using PSR-4

Let's say your app contains a src folder with a Test class in it like this:

    app/
        src/
            Test.php

```php
<?php
namespace MyApp;

class Test
{

}
```

Then, you can create an autoloader object and register your application's
source with it like this:

```php
require_once __DIR__ . '/../src/ClassLoader.php';

$autoloader = new Pop\Loader\ClassLoader();
$autoloader->addPsr4('MyApp\\', __DIR__ . '/../app/src');

$test = new MyApp\Test();
```

### Using PSR-0

There's also support for older the PSR-0 standard. If the folder structure and class was like this:

    app/
        MyApp/
            Test.php

```php
<?php
class MyApp_Test
{

}
```

Then, you can register it using PSR-0 like this:

```php
require_once __DIR__ . '/../src/ClassLoader.php';

$autoloader = new Pop\Loader\ClassLoader();
$autoloader->addPsr0('MyApp', __DIR__ . '/../app');

$test = new MyApp_Test();
```

### Using a class map

To generate a new class map:

```php
$mapper = new Pop\Loader\ClassMapper(__DIR__ . '/../app/src');
$mapper->writeToFile('classmap.php');
```

##### classmap.php

```php
<?php

return [
    'MyApp\Foo\Bar' => '/home/nick/Projects/pop/pop-loader/app/src/Foo/Bar.php',
    'MyApp\Thing' => '/home/nick/Projects/pop/pop-loader/app/src/Thing.php',
    'MyApp\Test' => '/home/nick/Projects/pop/pop-loader/app/src/Test.php'
];
```

To load an existing class map:

```php
$autoloader = new Pop\Loader\ClassLoader();
$autoloader->addClassMapFromFile('classmap.php');
```
