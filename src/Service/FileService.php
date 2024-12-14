<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Service;

use Exception;
use PhpParser\PrettyPrinter\Standard;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;

class FileService
{
    private Standard $prettyPrinter;

    public function __construct()
    {
        $this->prettyPrinter = new Standard();
    }

    /**
     * Save the code represented by the ClassPopo to its file path.
     *
     * @param ClassPopo $classPopo
     * @return void
     *
     * @throws Exception
     */
    public function save(ClassPopo $classPopo): void
    {
        $classPopo->syncAst(); // Ensure AST is up-to-date
        $code = $this->prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($classPopo->absolutePath, $code) === false) {
            throw new Exception("Failed to save the file: {$classPopo->absolutePath}");
        }
    }

    /**
     * Copy the ClassPopo to a new file path.
     *
     * @param ClassPopo $classPopo
     * @param string $newPath
     * @return ClassPopo
     *
     * @throws Exception
     */
    public function copy(ClassPopo $classPopo, string $newPath): ClassPopo
    {
        $classPopo->syncAst();
        $code = $this->prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($newPath, $code) === false) {
            throw new Exception("Failed to write the file: {$newPath}");
        }

        return new ClassPopo(
            $classPopo->name,
            $newPath,
            $classPopo->methods,
            $classPopo->properties,
            $classPopo->astNode
        );
    }

    /**
     * Print the PHP code represented by the ClassPopo as a string.
     *
     * @param ClassPopo $classPopo
     * @return string
     */
    public function print(ClassPopo $classPopo): string
    {
        $classPopo->syncAst();
        return $this->prettyPrinter->prettyPrintFile([$classPopo->astNode]);
    }

    /**
     * Save arbitrary AST nodes to a file.
     *
     * @param array $astNodes
     * @param string $filePath
     *
     * @return void
     * @throws Exception
     */
    public function saveAst(array $astNodes, string $filePath): void
    {
        $code = $this->prettyPrinter->prettyPrintFile($astNodes);

        if (file_put_contents($filePath, $code) === false) {
            throw new Exception("Failed to save the file: {$filePath}");
        }
    }

    /**
     * Print arbitrary AST nodes as a string.
     *
     * @param array $astNodes
     * @return string
     */
    public function printAst(array $astNodes): string
    {
        return $this->prettyPrinter->prettyPrintFile($astNodes);
    }

    /**
     * Validate that a file can be written to the specified path.
     *
     * @param string $filePath
     * @return void
     *
     * @throws Exception
     */
    public function validateWritable(string $filePath): void
    {
        $directory = dirname($filePath);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new Exception("Failed to create directory: {$directory}");
        }

        if (!is_writable($directory)) {
            throw new Exception("Directory is not writable: {$directory}");
        }
    }
}
