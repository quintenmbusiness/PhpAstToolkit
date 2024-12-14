<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Comment\Doc;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

class MethodPopo extends BasePopo
{
    public function __construct(
        string $name,
        string $visibility,
        public bool $isStatic,
        public bool $isFinal,
        public bool $isAbstract,
        public ?string $returnType,
        ?string $docComment,
        ClassMethod $astNode
    ) {
        parent::__construct($name, $astNode, $docComment, $visibility);
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
}