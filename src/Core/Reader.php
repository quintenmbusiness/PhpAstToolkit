<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Core;

use Exception;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;
use quintenmbusiness\PhpAstToolkit\Popo\MethodPopo;
use quintenmbusiness\PhpAstToolkit\Popo\PropertyPopo;

class Reader extends NodeVisitorAbstract
{
    protected array $classes = [];
    protected string $filePath;

    /**
     * Analyze the given PHP file path and extract class details.
     *
     * @param string $filePath
     * @return array<ClassPopo>
     * @throws Exception
     */
    public function analyzeFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $this->filePath = $filePath;

        $code = file_get_contents($filePath);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $ast = $parser->parse($code);

            if ($ast === null) {
                throw new Exception("Failed to parse the file: {$filePath}");
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor($this);
            $traverser->traverse($ast);

            return $this->getClasses();
        } catch (Error $e) {
            throw new Exception("Error parsing PHP file: {$e->getMessage()}");
        }
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $classPopo = new ClassPopo(
                $node->name->toString(),
                $this->filePath,
                $node,
                [],
                []
            );

            $this->extractMethods($node, $classPopo);
            $this->extractProperties($node, $classPopo);

            $this->classes[] = $classPopo;
        }
    }

    protected function extractMethods(Node\Stmt\Class_ $classNode, ClassPopo $classPopo): void
    {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $classPopo->addMethod(new MethodPopo(
                    $stmt->name->toString(),
                    $this->getVisibility($stmt),
                    $stmt->isStatic(),
                    $stmt->isFinal(),
                    $stmt->isAbstract(),
                    $stmt->getReturnType() ? $stmt->getReturnType()->toString() : null,
                    $stmt->getDocComment() ? $stmt->getDocComment()->getText() : null,
                    $stmt
                ));
            }
        }
    }

    protected function extractProperties(Node\Stmt\Class_ $classNode, ClassPopo $classPopo): void
    {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $property) {
                    $classPopo->addProperty(new PropertyPopo(
                        $property->name->toString(),
                        $this->getVisibility($stmt),
                        $stmt->type ? $stmt->type->toString() : null,
                        $stmt
                    ));
                }
            }
        }
    }

    protected function getVisibility(Node $node): string
    {
        if ($node->isPublic()) {
            return 'public';
        } elseif ($node->isProtected()) {
            return 'protected';
        } elseif ($node->isPrivate()) {
            return 'private';
        }
        return 'unknown';
    }

    public function getClasses(): array
    {
        return $this->classes;
    }
}