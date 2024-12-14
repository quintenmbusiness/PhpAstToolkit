<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Service;

use Exception;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Interface_;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;
use quintenmbusiness\PhpAstToolkit\Popo\MethodPopo;

class ClassService
{
    /**
     * @param ClassPopo $classPopo
     * @return void
     *
     * @throws Exception
     */
    public function save(ClassPopo $classPopo): void
    {
        $classPopo->syncAst();
        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($classPopo->absolutePath, $updatedCode) === false) {
            throw new Exception("Failed to save the file: {$classPopo->absolutePath}");
        }
    }

    /**
     * @param ClassPopo $classPopo
     * @param string $newPath
     *
     * @return ClassPopo
     * @throws Exception
     */
    public function copy(ClassPopo $classPopo, string $newPath): ClassPopo
    {
        $classPopo->syncAst();
        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$classPopo->astNode]);

        if (file_put_contents($newPath, $updatedCode) === false) {
            throw new Exception("Failed to write the file: {$newPath}");
        }

        return new ClassPopo($classPopo->name, $newPath, $classPopo->methods, $classPopo->properties,  $classPopo->astNode);
    }

    /**
     * @param ClassPopo $classPopo
     * @param string $newName
     * @param string $newPath
     * @param string|null $newExtends
     * @param array $newImplements
     * @param bool $updateOriginal
     *
     * @return void
     * @throws Exception
     */
    public function createInterfaceVersion(
        ClassPopo $classPopo,
        string $newName,
        string $newPath,
        ?string $newExtends = null,
        array $newImplements = [],
        bool $updateOriginal = false
    ): void {
        $interfaceMethods = $classPopo->methods->where('visibility', '=', 'public');

        $interfaceAstNode = new Interface_(
            $newName,
            [
                'stmts' => array_map(function (MethodPopo $method) {
                    return $method->toInterfaceAstNode();
                }, $interfaceMethods->toArray()),
                'extends' => $newExtends ? [new Name($newExtends)] : []
            ]
        );

        $prettyPrinter = new Standard();
        $updatedCode = $prettyPrinter->prettyPrintFile([$interfaceAstNode]);

        if (file_put_contents($newPath, $updatedCode) === false) {
            throw new Exception("Failed to save the interface file: {$newPath}");
        }

        if ($updateOriginal) {
            $classPopo->astNode->implements[] = new Name($newName);

            foreach ($interfaceMethods as $methodPopo) {
                $methodPopo->updateDocComment();
            }

            $this->save($classPopo);
        }
    }
}
