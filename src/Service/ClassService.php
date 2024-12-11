<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Service;

use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Interface_;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;
use quintenmbusiness\PhpAstToolkit\Popo\MethodPopo;

class ClassService
{
    public function save(ClassPopo $classPopo): void
    {
        $classPopo->syncAst(); // Ensure AST is up-to-date
        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($classPopo->absolutePath, $updatedCode) === false) {
            throw new \Exception("Failed to save the file: {$classPopo->absolutePath}");
        }
    }

    public function copy(ClassPopo $classPopo, string $newPath): ClassPopo
    {
        $classPopo->syncAst(); // Ensure AST is up-to-date
        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($newPath, $updatedCode) === false) {
            throw new \Exception("Failed to write the file: {$newPath}");
        }

        return new ClassPopo($classPopo->name, $newPath, $classPopo->astNode, $classPopo->methods, $classPopo->properties);
    }

    public function createInterfaceVersion(
        ClassPopo $classPopo,
        string $newName,
        string $newPath,
        ?string $newExtends = null,
        array $newImplements = [],
        bool $updateOriginal = false
    ): void {
        // Filter methods for interface: only public methods
        $interfaceMethods = array_filter($classPopo->methods, fn($method) => $method->visibility === 'public');

        // Create a new Interface AST node
        $interfaceAstNode = new Interface_(
            $newName,
            [
                'stmts' => array_map(function (MethodPopo $method) {
                    return $method->toInterfaceAstNode();
                }, $interfaceMethods),
                'extends' => $newExtends ? [new Name($newExtends)] : []
            ]
        );

        // Pretty print and save the interface
        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$interfaceAstNode]);

        if (file_put_contents($newPath, $updatedCode) === false) {
            throw new \Exception("Failed to save the interface file: {$newPath}");
        }

        // Optionally update the original class to implement the new interface
        if ($updateOriginal) {
            $classPopo->astNode->implements[] = new Name($newName);

            // Update PHPDocs for methods that are part of the interface
            foreach ($interfaceMethods as $methodPopo) {
                $methodPopo->updatePhpDocToInherit();
            }

            $this->save($classPopo);
        }
    }
}
