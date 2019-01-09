<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop\Loader
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.2
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
    protected $classMap = [];

    /**
     * Class map authoritative flag
     * @var boolean
     */
    protected $classMapAuthoritative = false;

    /**
     * Constructor
     *
     * Instantiate the class loader object
     *
     * @param  boolean $self
     * @param  boolean $prepend
     * @param  boolean $throw
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
    public function getPsr0Prefixes()
    {
        return array_keys($this->psr0);
    }

    /**
     * Get the PSR-4 prefixes
     *
     * @return array
     */
    public function getPsr4Prefixes()
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
        return $this->classMap;
    }

    /**
     * Add a class map array
     *
     * @param  array|ClassMapper $map
     * @throws Exception
     * @return ClassLoader
     */
    public function addClassMap($map)
    {
        if (!is_array($map) && !($map instanceof ClassMapper)) {
            throw new \InvalidArgumentException(
                'Error: The $map parameter must be a class map array or an instance of Pop\Loader\ClassMapper.'
            );
        }

        $this->classMap = ($map instanceof ClassMapper) ?
            array_merge($this->classMap, $map->getClassMap()) : array_merge($this->classMap, $map);

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

        $map      = new ClassMapper($dir);
        $classMap = $map->getClassMap();

        return $this->addClassMap($classMap);
    }

    /**
     * Set the class map as the authoritative loader, halting any searches via prefixes
     *
     * @param  boolean $authoritative
     * @return ClassLoader
     */
    public function setClassMapAuthoritative($authoritative)
    {
        $this->classMapAuthoritative = (bool)$authoritative;
        return $this;
    }

    /**
     * Determine if the class map is the authoritative loader
     *
     * @return boolean
     */
    public function isClassMapAuthoritative()
    {
        return $this->classMapAuthoritative;
    }

    /**
     * Add a PSR-0 prefix and directory location to the autoloader instance
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @throws Exception
     * @return ClassLoader
     */
    public function add($prefix, $directory, $prepend = false)
    {
        $dir = realpath($directory);

        if ($dir === false) {
            throw new Exception('That directory does not exist.');
        }

        if ($prepend) {
            $this->psr0 = array_merge([$prefix => $dir], $this->psr0);
        } else {
            $this->psr0[$prefix] = $dir;
        }

        return $this;
    }

    /**
     * Add a PSR-4 prefix and directory location to the autoloader instance
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @throws Exception
     * @return ClassLoader
     */
    public function addPsr4($prefix, $directory, $prepend = false)
    {
        $dir = realpath($directory);

        if ($dir === false) {
            throw new Exception('That directory does not exist.');
        }

        if (substr($prefix, -1) != '\\') {
            throw new Exception('The PSR-4 prefix must end with a namespace separator.');
        }

        if ($prepend) {
            $this->psr4 = array_merge([$prefix => $dir], $this->psr4);
        } else {
            $this->psr4[$prefix] = $dir;
        }

        return $this;
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @return ClassLoader
     */
    public function addPsr0($prefix, $directory, $prepend = false)
    {
        return $this->add($prefix, $directory, $prepend);
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @return ClassLoader
     */
    public function set($prefix, $directory, $prepend = false)
    {
        return $this->add($prefix, $directory, $prepend);
    }

    /**
     * Alias to add()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @return ClassLoader
     */
    public function setPsr0($prefix, $directory, $prepend = false)
    {
        return $this->add($prefix, $directory, $prepend);
    }

    /**
     * Alias to addPsr4()
     *
     * @param  string  $prefix
     * @param  string  $directory
     * @param  boolean $prepend
     * @return ClassLoader
     */
    public function setPsr4($prefix, $directory, $prepend = false)
    {
        return $this->addPsr4($prefix, $directory, $prepend);
    }

    /**
     * Find the class file
     *
     * @param  string $class
     * @return mixed
     */
    public function findFile($class)
    {
        $psr4Prefix = null;
        $psr0Prefix = null;
        $separator  = (strpos($class, '\\') !== false) ? '\\' : '_';

        // Check the class map for the class
        if (array_key_exists($class, $this->classMap)) {
            return realpath($this->classMap[$class]);
        }

        // If class map is the authoritative loader, stop searching
        if ($this->classMapAuthoritative) {
            return false;
        }

        // Sort array by key length, descending
        array_multisort(array_map('strlen', array_keys($this->psr4)), SORT_DESC, $this->psr4);

        // Try and detect a PSR-4 prefix
        foreach ($this->psr4 as $key => $value) {
            if (substr($class, 0, strlen($key)) == $key) {
                $psr4Prefix = $key;
                break;
            }
        }

        if (null !== $psr4Prefix) {
            $psr4ClassFile = str_replace($separator, DIRECTORY_SEPARATOR, substr($class, strlen($psr4Prefix))) . '.php';
            return realpath($this->psr4[$psr4Prefix] . DIRECTORY_SEPARATOR . $psr4ClassFile);
        }

        // Sort array by key length, descending
        array_multisort(array_map('strlen', array_keys($this->psr0)), SORT_DESC, $this->psr0);

        // Try and detect a PSR-0 prefix
        $psr0ClassFile = str_replace($separator, DIRECTORY_SEPARATOR, $class) . '.php';
        foreach ($this->psr0 as $key => $value) {
            if (substr($class, 0, strlen($key)) == $key) {
                $psr0Prefix = $key;
                break;
            }
        }
        if (null !== $psr0Prefix) {
            return realpath($this->psr0[$psr0Prefix] . DIRECTORY_SEPARATOR . $psr0ClassFile);
        }

        // Else, nothing found, return false
        return false;
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
