<?php

/*
 * This file is part of the BeSimpleSoap.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\SoapBundle\ServiceDefinition\Loader;

use BeSimple\SoapBundle\ServiceDefinition\Definition;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;

use function count;
use function function_exists;
use function in_array;
use function is_array;
use function is_string;
use function token_get_all;

use const PATHINFO_EXTENSION;
use const T_CLASS;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;

/**
 * AnnotationFileLoader loads ServiceDefinition from annotations set
 * on a PHP class and its methods.
 *
 * Based on \Symfony\Component\Routing\Loader\AnnotationFileLoader
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class AnnotationFileLoader extends FileLoader
{
    protected AnnotationClassLoader $loader;

    public function __construct(FileLocator $locator, AnnotationClassLoader $loader)
    {
        if (!function_exists('token_get_all')) {
            throw new RuntimeException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        parent::__construct($locator);

        $this->loader = $loader;
    }

    /**
     * Loads from annotations from a file.
     *
     * @throws InvalidArgumentException When the file does not exist
     * @throws Exception
     */
    public function load(mixed $resource, ?string $type = null): ?Definition
    {
        $path = $this->locator->locate($resource);

        if ($class = $this->findClass($path)) {
            return $this->loader->load($class, $type);
        }

        return null;
    }

    /**
     * Returns true if this class supports the given resource.
     */
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     */
    protected function findClass($file): string
    {
        return $this->getClassFullNameFromFile($file);
    }

    /**
     * Thanks to https://stackoverflow.com/a/39887697 we found a solution which also works in PHP8
     *
     * get the full name (name \ namespace) of a class from its file path
     * result example: (string) "I\Am\The\Namespace\Of\This\Class"
     */
    public function getClassFullNameFromFile(string $filePathName): string
    {
        return $this->getClassNamespaceFromFile($filePathName) . '\\' . $this->getClassNameFromFile($filePathName);
    }

    /**
     * get the class namespace form file path using token
     */
    protected function getClassNamespaceFromFile(string $file): ?string
    {
        $src = file_get_contents($file);

        $tokens = token_get_all($src);
        $count = count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if (!$namespace_ok) {
            return null;
        }

        return $namespace;
    }

    /**
     * get the class name form file path using token
     */
    protected function getClassNameFromFile(string $filePathName): ?string
    {
        $php_code = file_get_contents($filePathName);

        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] === T_CLASS
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes[0];
    }
}
