<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Core;

use Exception;
use quintenmbusiness\PhpAstToolkit\Popo\DirectoryPopo;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;
use Tightenco\Collect\Support\Collection;

class DirectoryScanner
{
    private string $baseDir;

    /**
     * Default excluded directories.
     *
     * @var array<string>
     */
    private array $excludedDirs = ['vendor', 'tests', '.git', '.idea', 'node_modules'];

    /**
     * DirectoryScanner constructor.
     */
    public function __construct()
    {
        $this->baseDir = $this->resolveBaseDir();
    }

    /**
     * Resolves the base directory of the project.
     *
     * @return string
     */
    private function resolveBaseDir(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Scans a directory for PHP files and organizes them into DirectoryPopos.
     *
     * @param string|null $directory
     * @param bool $recursive
     * @param array<string> $exclude
     *
     * @return Collection<DirectoryPopo>
     * @throws Exception
     */
    public function scan(?string $directory = null, bool $recursive = false, array $exclude = []): Collection
    {
        $directory = $directory ?? $this->baseDir;
        $exclude = array_merge($this->excludedDirs, $exclude);

        if (!is_dir($directory)) {
            throw new Exception("Invalid directory: {$directory}");
        }

        return $this->getDirectoryPopos($directory, $recursive, $exclude);
    }

    /**
     * Retrieves DirectoryPopos for a directory and its subdirectories.
     *
     * @param string $directory
     * @param bool $recursive
     * @param array<string> $exclude
     *
     * @return Collection<DirectoryPopo>
     * @throws Exception
     */
    private function getDirectoryPopos(string $directory, bool $recursive, array $exclude): Collection
    {
        $directories = new Collection;
        $files = new Collection;

        $iterator = new \DirectoryIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }

            $path = $file->getPathname();

            if ($file->isDir() && $this->isExcluded($path, $exclude)) {
                continue;
            }

            if ($file->isDir()) {
                $subdirectories = $recursive
                    ? $this->getDirectoryPopos($path, $recursive, $exclude)
                    : new Collection;


                $subdirectoryClasses = $subdirectories->flatMap(fn($subdirectory) => $subdirectory->classes);

                $directories->push(new DirectoryPopo(
                    $file->getBasename(),
                    $path,
                    classes: $subdirectoryClasses,
                    subdirectories: $subdirectories
                ));
            } elseif ($file->isFile() && $file->getExtension() == 'php') {
                $files->push($path);
            }
        }

        $classes = $this->processFiles($files);

        $currentDirectory = new DirectoryPopo(
            name: basename($directory),
            absolutePath: $directory,
            classes: $classes,
            subdirectories: $directories
        );

        return collect([$currentDirectory]);
    }

    /**
     * Processes PHP files and extracts ClassPopos using the Reader.
     *
     * @param Collection<string> $files
     *
     * @return Collection<ClassPopo>
     * @throws Exception
     */
    public function processFiles(Collection $files): Collection
    {
        $reader = new Reader();


        return $files->flatMap(function (string $filePath) use ($reader) {
            try {
                $classes = $reader->analyzeFile($filePath);

                return $classes;
            } catch (Exception $e) {
                return new Collection;
            }
        });
    }

    /**
     * Recursively scans directories and collects classes.
     *
     * @param Collection $directories
     * @return Collection
     */
    public function collectClassesFromDirectories(Collection $directories): Collection
    {
        $classes = new Collection;

        foreach ($directories as $directory) {

            foreach ($directory->classes as $class) {
                $classes->push($class);
            }

            if (!empty($directory->subdirectories)) {
                $classes = $classes->merge($this->collectClassesFromDirectories($directory->subdirectories));
            }
        }

        return $classes;
    }

    /**
     * Checks if a directory is excluded based on the exclude list.
     *
     * @param string $path
     * @param array<string> $exclude
     *
     * @return bool
     */
    private function isExcluded(string $path, array $exclude): bool
    {
        $normalizedPath = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        foreach ($exclude as $excludedDir) {
            $normalizedExcluded = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $excludedDir), DIRECTORY_SEPARATOR);

            if (stripos($normalizedPath, DIRECTORY_SEPARATOR . $normalizedExcluded . DIRECTORY_SEPARATOR) !== false ||
                substr_compare($normalizedPath, $normalizedExcluded, -strlen($normalizedExcluded), strlen($normalizedExcluded), true) === 0) {
                return true;
            }
        }

        return false;
    }
}
