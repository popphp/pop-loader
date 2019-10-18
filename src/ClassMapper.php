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
 * Mapper class
 *
 * @category   Pop
 * @package    Pop\Loader
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.2
 */
class ClassMapper
{

    /**
     * Source directories
     * @var array
     */
    protected $sources = [];

    /**
     * Class map
     * @var array
     */
    protected $map = [];

    /**
     * Files
     * @var array
     */
    protected $files = [];

    /**
     * Constructor
     *
     * Instantiate the class mapper object
     *
     * @param  mixed $source
     */
    public function __construct($source = null)
    {
        if (null !== $source) {
            $this->addSource($source);
            $this->generateClassMap();
        }
    }

    /**
     * Add source directory or directories
     *
     * @param  mixed $source
     * @throws Exception
     * @return ClassMapper
     */
    public function addSource($source)
    {
        if (!is_array($source)) {
            $source = [$source];
        }

        foreach ($source as $src) {
            if (!file_exists($src)) {
                throw new Exception('Error: That source folder does not exist.');
            }
            if (!$this->hasSource($src)) {
                $this->sources[] = $src;
            }
        }

        return $this;
    }

    /**
     * Get sources
     *
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Determine if a source directory has been added
     *
     * @param  string  $source
     * @return boolean
     */
    public function hasSource($source)
    {
        return in_array($source, $this->sources);
    }

    /**
     * Clear sources
     *
     * @return ClassMapper
     */
    public function clearSources()
    {
        $this->sources = [];
        return $this;
    }

    /**
     * Generate a class map
     *
     * @return ClassMapper
     */
    public function generateClassMap()
    {
        $this->discoverFiles();

        foreach ($this->files as $file) {
            $classMatch        = [];
            $namespaceMatch    = [];
            $classFileContents = file_get_contents($file);

            preg_match('/^(abstract|interface|trait|class)(.*)$/m', $classFileContents, $classMatch);
            preg_match('/^namespace(.*)$/m', $classFileContents, $namespaceMatch);

            if (isset($classMatch[0])) {
                if (strpos($classMatch[0], 'abstract') !== false) {
                    $class = str_replace('abstract class ', '', $classMatch[0]);
                } else if (strpos($classMatch[0], 'interface') !== false) {
                    $class = str_replace('interface ', '', $classMatch[0]);
                } else if (strpos($classMatch[0], 'trait') !== false) {
                    $class = str_replace('trait ', '', $classMatch[0]);
                } else {
                    $class = str_replace('class ', '', $classMatch[0]);
                }

                if (strpos($class, ' ') !== false) {
                    $class = substr($class, 0, strpos($class, ' '));
                }

                $class = trim($class);
                if (isset($namespaceMatch[0])) {
                    $class = trim(str_replace(';', '', str_replace('namespace ', '', $namespaceMatch[0]))) . '\\' . $class;
                }

                $this->map[$class] = str_replace('\\', '/', $file);
            }
        }

        return $this;
    }

    /**
     * Get the class map
     *
     * @return array
     */
    public function getClassMap()
    {
        return $this->map;
    }

    /**
     * Write a class map to an output file
     *
     * @param  string $output
     * @return ClassMapper
     */
    public function writeToFile($output)
    {
        $code = '<?php' . PHP_EOL . PHP_EOL . 'return [';

        $i = 1;
        foreach ($this->map as $class => $file) {
            $comma = ($i < count($this->map)) ? ',' : null;
            $code .= PHP_EOL . '    \'' . $class . '\' => \'' . $file . '\'' . $comma;
            $i++;
        }

        $code .= PHP_EOL . '];' . PHP_EOL;

        file_put_contents($output, $code);

        return $this;
    }

    /**
     * Discover files from source directory
     *
     * @return void
     */
    protected function discoverFiles()
    {
        foreach ($this->sources as $source) {
            $objects = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($objects as $fileInfo) {
                if (($fileInfo->getFilename() != '.') && ($fileInfo->getFilename() != '..')) {
                    $f = null;
                    if (!$fileInfo->isDir()) {
                        $f = realpath($fileInfo->getPathname());
                    }
                    if (($f !== false) && (null !== $f) && (substr(strtolower($f), -4) == '.php')) {
                        $this->files[] = $f;
                    }
                }
            }
        }
    }

}
