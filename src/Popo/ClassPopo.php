<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Node\Stmt\Class_;
use Tightenco\Collect\Support\Collection;

class ClassPopo extends BasePopo
{
    /**
     * @var Collection<MethodPopo>
     */
    public Collection $methods;

    /**
     * @var Collection<PropertyPopo>
     */
    public Collection $properties;

    public function __construct(
        string $name,
        public string $absolutePath,
        Collection $methods,
        Collection $properties,
        Class_ $astNode
    ) {
        parent::__construct($name, $astNode);
        $this->methods = collect($methods);
        $this->properties = collect($properties);
    }

    /**
     * Get the directory path of the class file.
     *
     * @return string
     */
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
        $this->astNode->stmts = $this->properties->merge($this->methods)->map(fn($popo) => $popo->astNode)->toArray();
    }
}
