<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Node\Stmt\Class_;

class ClassPopo
{
    public function __construct(
        public string $name,
        public string $absolutePath,
        public Class_ $astNode,
        /**
         * @var MethodPopo[] Array of method popos.
         */
        public array $methods = [],
        /**
         * @var PropertyPopo[] Array of property popos.
         */
        public array $properties = []
    ) {}

    public function addMethod(MethodPopo $method): void
    {
        $this->methods[] = $method;
    }

    public function addProperty(PropertyPopo $property): void
    {
        $this->properties[] = $property;
    }

    public function updateName(string $newName): void
    {
        $this->name = $newName;
        $this->astNode->name->name = $newName;
    }

    public function getDirectory(): string
    {
        return dirname($this->absolutePath);
    }

    /**
     * Synchronize the AST to ensure it reflects current properties and methods.
     *
     * @return void
     */
    public function syncAst(): void
    {
        $this->astNode->stmts = [];

        foreach ($this->methods as $methodPopo) {
            $this->astNode->stmts[] = $methodPopo->astNode;
        }

        foreach ($this->properties as $propertyPopo) {
            $this->astNode->stmts[] = $propertyPopo->astNode;
        }
    }
}
