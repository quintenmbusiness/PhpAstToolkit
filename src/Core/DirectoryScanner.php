<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Core;

use Exception;
use PhpParser\Error;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;

class DirectoryScanner
{
    public function scan(string $directory, bool $recursive = false): array
    {
        if (!is_dir($directory)) {
            throw new Exception("Invalid directory: {$directory}");
        }

        $files = $this->getPhpFiles($directory, $recursive);
        $reader = new Reader();

        $classes = [];
        foreach ($files as $file) {
            try {
                $classes = array_merge($classes, $reader->analyzeFile($file));
            } catch (Error $e) {
                echo "Error parsing file {$file}: {$e->getMessage()}\n";
            }
        }

        return $classes;
    }

    private function getPhpFiles(string $directory, bool $recursive = false): array
    {
        $files = [];
        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        } else {
            $iterator = new \DirectoryIterator($directory);
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        return $files;
    }
}
