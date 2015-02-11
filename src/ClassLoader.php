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
     * @param  boolean $self
     * @param  boolean $prepend
     * @param  boolean $throw
     * @return ClassLoader
     */
    public function __construct($self = true, $prepend = false, $throw = true)
    {
        if ($self) {
            $this->addPsr4('Pop\Loader\\', __DIR__);
            $this->register($prepend, $throw);
        }
    }

    /**
     * Register this instance with the autoload stack
     *
     * @param  boolean $prepend
     * @param  boolean $throw
     * @return ClassLoader
     */
    public function register($prepend, $throw = true)
    {
        spl_autoload_register($this, (bool)$throw, (bool)$prepend);
        return $this;
    }

    /**
     * Unregister this instance with the autoload stack
     *
     * @return ClassLoader
     */
    public function unregister()
    {
        spl_autoload_unregister($this);
        return $this;
    }

    /**
     * Get the PSR-0 prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        return array_keys($this->psr0);
    }

    /**
     * Get the PSR-0 prefixes
     *
     * @return array
     */
    public function getPrefixesPsr4()
    {
        return array_keys($this->psr4);
    }

    /**
     * Get the class map array
     *
     * @return array
     */
    public function getClassMap()
    {
        return $this->classmap;
    }

    /**
     * Add a class map array
     *
     * @param  array $map
     * @throws Exception
     * @return ClassLoader
     */
    public function addClassMap(array $map)
    {
        $this->classmap = array_merge($this->classmap, $map);
        return $this;
    }

    /**
     * Add a class map from file
     *
     * @param  string $file
     * @throws Exception
     * @return ClassLoader
     */
    public function addClassMapFromFile($file)
    {
        if (!file_exists($file)) {
            throw new Exception('That class map file does not exist.');
        }

        $classMap = include $file;

        if (!is_array($classMap)) {
            throw new Exception('The class map file did not return an array.');
        }

        return $this->addClassMap($classMap);
    }

    /**
     * Generate and add a class map from directory
     *
     * @param  string $dir
     * @throws Exception
     * @return ClassLoader
     */
    public function addClassMapFromDir($dir)
    {
        if (!file_exists($dir)) {
            throw new Exception('That class map directory does not exist.');
        }

        $map      = new ClassMapper('/home/nick/Desktop/Pop');
        $classMap = $map->getClassMap();

        if (!is_array($classMap)) {
            throw new Exception('The class map directory did not parse correctly and return an array.');
        }

        return $this->addClassMap($classMap);
    }

    /**
     * Register a PSR-0 prefix and directory location with the autoloader
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @throws Exception
     * @return ClassLoader
     */
    public function add($prefix, $directory)
    {
        $dir = realpath($directory);

        if ($dir === false) {
            throw new Exception('That directory does not exist.');
        }

        $this->psr0[$prefix] = $dir;

        return $this;
    }

    /**
     * Register a PSR-4 prefix and directory location with the autoloader
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @throws Exception
     * @return ClassLoader
     */
    public function addPsr4($prefix, $directory)
    {
        $dir = realpath($directory);

        if ($dir === false) {
            throw new Exception('That directory does not exist.');
        }

        if (substr($prefix, -1) != '\\') {
            throw new Exception('The PSR-4 prefix must end with a namespace separator.');
        }

        $this->psr4[$prefix] = $dir;

        return $this;
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function addPsr0($prefix, $directory)
    {
        return $this->add($prefix, $directory);
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function set($prefix, $directory)
    {
        return $this->add($prefix, $directory);
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function setPsr0($prefix, $directory)
    {
        return $this->add($prefix, $directory);
    }

    /**
     * Alias to addPsr4()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @return ClassLoader
     */
    public function setPsr4($prefix, $directory)
    {
        return $this->addPsr4($prefix, $directory);
    }

    /**
     * Find the class file
     *
     * @param  string $class
     * @return mixed
     */
    public function findFile($class)
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

        return $classFile;
    }

    /**
     * Find and load the class file
     *
     * @param  string $class
     * @return boolean
     */
    public function loadClass($class)
    {
        $classFile = $this->findFile($class);
        if ($classFile !== false) {
            include $classFile;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Invoke the class
     *
     * @param  string $class
     * @return void
     */
    public function __invoke($class)
    {
        $this->loadClass($class);
    }

}
