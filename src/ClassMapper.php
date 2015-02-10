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
 * Mapper class
 *
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class ClassMapper
{

    /**
     * Source directory
     * @var string
     */
    protected $source = null;

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
     * @param  string $source
     * @throws Exception
     * @return ClassMapper
     */
    public function __construct($source)
    {
        if (!file_exists($source)) {
            throw new Exception('Error: That source folder does not exist.');
        }
        $this->source = $source;
        $this->generateClassMap();
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

            preg_match('/^(abstract|interface|class)(.*)$/m', $classFileContents, $classMatch);
            preg_match('/^namespace(.*)$/m', $classFileContents, $namespaceMatch);

            if (isset($classMatch[0])) {
                if (strpos($classMatch[0], 'abstract') !== false) {
                    $class = str_replace('abstract class ', '', $classMatch[0]);
                } else if (strpos($classMatch[0], 'interface') !== false) {
                    $class = str_replace('interface ', '', $classMatch[0]);
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
    }

    /**
     * Discover files from source directory
     *
     * @return void
     */
    protected function discoverFiles()
    {
        $objects = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->source), \RecursiveIteratorIterator::SELF_FIRST
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
