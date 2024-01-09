<?php

declare(strict_types=1);

namespace Borlabs;

final class Autoloader
{
    private static ?Autoloader $instance = null;

    public static function getInstance(): Autoloader
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @var array<string>
     */
    private array $prefixes = [];

    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        $prefix = trim($prefix, '\\') . '\\';

        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }

        if ($prepend === false) {
            array_push($this->prefixes[$prefix], $baseDir);
        } else {
            array_unshift($this->prefixes[$prefix], $baseDir);
        }
    }

    public function loadClass(string $class): bool
    {
        $prefix = $class;

        while (($pos = strrpos($prefix, '\\')) !== false) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);
            $fileLoaded = $this->loadFile($prefix, $relativeClass);

            if ($fileLoaded) {
                return true;
            }

            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    public function loadFile(string $prefix, string $relativeClass): bool
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        $relativeClass = str_replace('\\', '/', $relativeClass);

        foreach ($this->prefixes[$prefix] as $baseDir) {
            $file = $baseDir . $relativeClass . '.php';

            if ($this->requireFile($file)) {
                return true;
            }
        }

        return false;
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }
}
