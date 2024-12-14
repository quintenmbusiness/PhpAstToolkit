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
    /**
     * @var array<ClassPopo>
     */
    protected array $classes = [];

    /**
     * @var string
     */
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

    /**
     * @param Node $node
     * @return void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $classPopo = new ClassPopo(
                $node->name->toString(),
                $this->filePath,
                collect(),
                collect(),
                $node,
            );

            $this->extractMethods($node, $classPopo);
            $this->extractProperties($node, $classPopo);

            $this->classes[] = $classPopo;
        }
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     * @param ClassPopo $classPopo
     *
     * @return void
     */
    protected function extractMethods(Node\Stmt\Class_ $classNode, ClassPopo $classPopo): void
    {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $classPopo->methods->add(new MethodPopo(
                    $stmt->name->toString(),
                    $this->getVisibility($stmt),
                    $stmt->isStatic(),
                    $stmt->isFinal(),
                    $stmt->isAbstract(),
                    $stmt->getReturnType() ? $this->getTypeString($stmt->getReturnType()) : null,
                    $stmt->getDocComment() ? $stmt->getDocComment()->getText() : null,
                    $stmt
                ));
            }
        }
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     * @param ClassPopo $classPopo
     *
     * @return void
     */
    protected function extractProperties(Node\Stmt\Class_ $classNode, ClassPopo $classPopo): void
    {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                foreach ($stmt->props as $property) {
                    $classPopo->properties->add(new PropertyPopo(
                        $property->name->toString(),
                        $this->getVisibility($stmt),
                        $stmt->type ? $this->getTypeString($stmt->type) : null,
                        $stmt
                    ));
                }
            }
        }
    }

    /**
     * Converts a type node to a string, handling nullable types and others.
     *
     * @param Node $typeNode
     * @return string
     */
    protected function getTypeString(Node $typeNode): string
    {
        if ($typeNode instanceof Node\NullableType) {
            return '?' . $this->getTypeString($typeNode->type); // Recursively handle inner type
        }

        if ($typeNode instanceof Node\Identifier || $typeNode instanceof Node\Name) {
            return $typeNode->toString();
        }

        return 'mixed'; // Default fallback for unsupported or unknown types
    }

    /**
     * @param Node $node
     *
     * @return string
     */
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

    /**
     * @return ClassPopo[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
