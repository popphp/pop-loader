<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Loader;

/**
 * Loader class
 *
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class ClassLoader
{

    /**
     * PSR-0 prefixes
     * @var array
     */
    protected $psr0 = [];

    /**
     * PSR-4 prefixes
     * @var array
     */
    protected $psr4 = [];

    /**
     * Class map array
     * @var array
     */
    protected $classmap = [];

    /**
     * Strict flag
     * @var boolean
     */
    protected $strict = true;

    /**
     * Constructor
     *
     * Instantiate the class loader object
     *
     * @param  boolean $fallback
     * @param  boolean $throw
     * @param  boolean $strict
     * @return ClassLoader
     */
    public function __construct($fallback = false, $throw = true, $strict = true)
    {
        $this->register('Pop\Loader\\', __DIR__);
        spl_autoload_register($this, $throw, (!$fallback));
        $this->strict = (bool)$strict;
    }

    /**
     * Load a class map
     *
     * @param  array $map
     * @throws Exception
     * @return ClassLoader
     */
    public function loadClassMap(array $map)
    {
        $this->classmap = array_merge($this->classmap, $map);
        return $this;
    }

    /**
     * Load a class map from file
     *
     * @param  string $file
     * @throws Exception
     * @return ClassLoader
     */
    public function loadClassMapFromFile($file)
    {
        if (!file_exists($file)) {
            throw new Exception('That class map file does not exist.');
        }

        $classMap = include $file;

        if (!is_array($classMap)) {
            throw new Exception('The class map file did not return an array.');
        }

        return $this->loadClassMap($classMap);
    }

    /**
     * Load a class map from directory
     *
     * @param  string $dir
     * @throws Exception
     * @return ClassLoader
     */
    public function loadClassMapFromDir($dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('That class map directory does not exist.');
        }

        $map      = new ClassMapper('/home/nick/Desktop/Pop');
        $classMap = $map->getClassMap();

        if (!is_array($classMap)) {
            throw new Exception('The class map directory did not parse correctly and return an array.');
        }

        return $this->loadClassMap($classMap);
    }

    /**
     * Register a prefix and directory location with the autoloader
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $psr4
     * @throws Exception
     * @return ClassLoader
     */
    public function register($prefix, $directory, $psr4 = true)
    {
        $dir = realpath($directory);

        if ($dir === false) {
            throw new Exception('That directory does not exist.');
        }

        if ($psr4) {
            $this->psr4[$prefix] = $dir;
        } else {
            $this->psr0[$prefix] = $dir;
        }

        return $this;
    }

    /**
     * Register a PSR-4 prefix and directory location with the autoloader
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function registerPsr4($prefix, $directory)
    {
        return $this->register($prefix, $directory);
    }

    /**
     * Register a PSR-0 prefix and directory location with the autoloader
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function registerPsr0($prefix, $directory)
    {
        return $this->register($prefix, $directory, false);
    }

    /**
     * Invoke the class
     *
     * @param  string $class
     * @throws Exception
     * @return void
     */
    public function __invoke($class)
    {
        $classFile  = false;
        $psr4Prefix = null;
        $psr0Prefix = null;
        $separator  = (strpos($class, '\\') !== false) ? '\\' : '_';

        // Check the class map property
        if (array_key_exists($class, $this->classmap)) {
            $classFile = realpath($this->classmap[$class]);
        // Else, try and auto-detect the class called
        } else {
            // Try and detect a PSR-4 prefix
            foreach ($this->psr4 as $key => $value) {
                if (substr($class, 0, strlen($key)) == $key) {
                    $psr4Prefix = $key;
                }
            }

            // If PSR-4 prefix detected
            if (null !== $psr4Prefix) {
                $psr4ClassFile = str_replace($separator, DIRECTORY_SEPARATOR, substr($class, strlen($psr4Prefix))) . '.php';
                $classFile     = realpath($this->psr4[$psr4Prefix] . DIRECTORY_SEPARATOR . $psr4ClassFile);
            // Else, try to detect a PSR-0 prefix
            } else {
                $psr0ClassFile = str_replace($separator, DIRECTORY_SEPARATOR, $class) . '.php';
                foreach ($this->psr0 as $key => $value) {
                    if (substr($class, 0, strlen($key)) == $key) {
                        $psr0Prefix = $key;
                    }
                }
                if (null !== $psr0Prefix) {
                    $classFile = realpath($this->psr0[$psr0Prefix] . DIRECTORY_SEPARATOR . $psr0ClassFile);
                }
            }
        }

        if ($classFile !== false) {
            include_once $classFile;
        } else if ($this->strict) {
            throw new Exception("The class file '" . $classFile . "' does not exist.");
        }
    }

}
