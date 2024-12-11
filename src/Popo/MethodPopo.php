<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Comment\Doc;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class MethodPopo
{
    /**
     * @param string $name Name of the method.
     * @param string $visibility Visibility of the method (public, protected, private).
     * @param bool $isStatic Whether the method is static.
     * @param bool $isFinal Whether the method is final.
     * @param bool $isAbstract Whether the method is abstract.
     * @param string|null $returnType The return type of the method.
     * @param string|null $docComment The doc comment for the method.
     * @param ClassMethod $astNode The raw AST node for the method.
     */
    public function __construct(
        public string $name,
        public string $visibility,
        public bool $isStatic,
        public bool $isFinal,
        public bool $isAbstract,
        public ?string $returnType,
        public ?string $docComment,
        public ClassMethod $astNode
    ) {}

    /**
     * Update the method's name.
     *
     * @param string $newName
     * @return void
     */
    public function updateName(string $newName): void
    {
        $this->name = $newName;
        $this->astNode->name->name = $newName;
    }

    /**
     * Convert the method to an interface-compatible AST node.
     *
     * @param bool $inheritPhpDoc If true, adds @inheritdoc to the method's PHPDoc.
     * @return ClassMethod
     */
    public function toInterfaceAstNode(bool $inheritPhpDoc = false): ClassMethod
    {
        if ($this->visibility !== 'public') {
            throw new \LogicException("Only public methods can be part of an interface.");
        }

        $methodNode = new ClassMethod(
            $this->name,
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => $this->returnType ? new Name($this->returnType) : null,
                'stmts' => null, // No body for interface methods
            ]
        );

        // Add @inheritdoc if required
        if ($inheritPhpDoc) {
            $methodNode->setDocComment(new Doc("/**\n * @inheritdoc\n */"));
        } elseif ($this->docComment) {
            // Retain the original PHPDoc if no inheritance is specified
            $methodNode->setDocComment(new Doc($this->docComment));
        }

        return $methodNode;
    }

    public function updatePhpDocToInherit(): void
    {
        $this->docComment = '@inheritdoc';

        // Update the AST node's doc comment
        $this->astNode->setDocComment(new Doc("/**\n * @inheritdoc\n */"));
    }
}