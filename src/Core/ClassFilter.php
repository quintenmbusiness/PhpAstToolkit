<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Core;

use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;
use Tightenco\Collect\Support\Collection;

class ClassFilter
{
    /**
     * @var Collection<int, ClassPopo>
     */
    private Collection $classes;

    /**
     * @param Collection<int, ClassPopo> $classes
     */
    public function __construct(Collection $classes)
    {
        $this->classes = $classes;
    }

    /**
     * @param string $className
     * @return Collection<int, ClassPopo>
     */
    public function searchByClassName(string $className): Collection
    {
        return $this->classes->filter(fn(ClassPopo $class) => stripos($class->name, $className) !== false);
    }

    /**
     * @param string $methodName
     * @return Collection<int, ClassPopo>
     */
    public function filterByMethodName(string $methodName): Collection
    {
        return $this->classes->filter(fn(ClassPopo $class) =>
        $class->methods->filter(fn($method) => stripos($method->getName(), $methodName) !== false)->isNotEmpty()
        );
    }

    /**
     * @param string $propertyName
     * @return Collection
     */
    public function filterByPropertyName(string $propertyName): Collection
    {
        return $this->classes->filter(fn(ClassPopo $class) =>
        $class->properties->filter(fn($property) => stripos($property->getName(), $propertyName) !== false)->isNotEmpty()
        );
    }

    /**
     * @return void
     */
    public function printSummary(): void
    {
        $this->classes->each(function (ClassPopo $class) {
            echo "Class: {$class->name}\n";
            echo "Methods: " . $class->methods->map(fn($m) => $m->getName())->implode(', ') . "\n";
            echo "Properties: " . $class->properties->map(fn($p) => $p->getName())->implode(', ') . "\n";
        });
    }
}
