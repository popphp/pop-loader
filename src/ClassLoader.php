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
     * Constructor
     *
     * Instantiate the class loader object
     *
     * @param  string  $map
     * @param  boolean $fallback
     * @return ClassLoader
     */
    public function __construct($map = null, $fallback = false)
    {
        if ($fallback) {
            spl_autoload_register($this, true, false);
        } else {
            spl_autoload_register($this, true, true);
        }

        if (null !== $map) {
            $this->loadClassMap($map);
        }
    }

    /**
     * Load a class map file
     *
     * @param  string $map
     * @throws Exception
     * @return ClassLoader
     */
    public function loadClassMap($map)
    {
        if (!file_exists($map)) {
            throw new Exception('That class map file does not exist.');
        }

        $classMap = include $map;

        if (!is_array($classMap)) {
            throw new Exception('The class map file did not return an array.');
        }

        $this->classmap = array_merge($this->classmap, $classMap);

        return $this;
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
        if (array_key_exists($class, $this->classmap)) {
            $class = realpath($this->classmap[$class]);
            if ($class === false) {
                throw new Exception('That class file does not exist.');
            }
            require_once $class;
        } else {
            $prefix    = null;
            $separator = (strpos($class, '\\') !== false) ? '\\' : '_';
            $classFile = str_replace($separator, DIRECTORY_SEPARATOR, $class) . '.php';

            // Check the PSR-4 prefixes
            foreach ($this->psr4 as $key => $value) {
                if (substr($class, 0, strlen($key)) == $key) {
                    $prefix = $key;
                }
            }

            // Check the PSR-0 prefixes
            if (null === $prefix) {
                foreach ($this->psr0 as $key => $value) {
                    if (substr($class, 0, strlen($key)) == $key) {
                        $prefix = $key;
                    }
                }
            }

            if (null === $prefix) {
                throw new Exception('Unable to map that class file.');
            } else {
                $classFile = $this->prefixes[$prefix] . DIRECTORY_SEPARATOR . $classFile;
                if (!include_once($classFile)) {
                    return;
                }
            }

        }
    }

}
