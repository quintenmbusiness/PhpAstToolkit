<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Comment\Doc;
use PhpParser\Node;

abstract class BasePopo
{
    /**
     * @param string $name
     * @param Node $astNode
     * @param string|null $docComment
     * @param string|null $visibility
     */
    public function __construct(
        public string $name,
        public Node $astNode,
        public ?string $docComment = null,
        public ?string $visibility = null
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
     * Update the doc comment.
     * You can leave empty for it to use inheritDoc
     *
     * @param string $docComment
     * @return void
     */
    public function updateDocComment(string $docComment = "@inheritdoc"): void
    {
        $this->docComment = $docComment;

        if($docComment === "@inheritdoc") {
            $this->astNode->setDocComment(new Doc("/**\n * @inheritdoc\n */"));
        }

        $this->astNode->setDocComment($docComment ? new Doc($docComment) : null);
    }

    /**
     * Update the visibility of the element.
     *
     * @param string $newVisibility
     * @return void
     */
    public function updateVisibility(string $newVisibility): void
    {
        $this->visibility = $newVisibility;
    }
}