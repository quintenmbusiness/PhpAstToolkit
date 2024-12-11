<?php

declare(strict_types=1);

namespace quintenmbusiness\PhpAstToolkit\Core;

use quintenmbusiness\PhpAstToolkit\Popo\ClassPopo;

class ClassFilter
{
    /**
     * @var ClassPopo[]
     */
    private array $classes;

    public function __construct(array $classes)
    {
        $this->classes = $classes;
    }

    public function searchByClassName(string $className): array
    {
        return array_filter($this->classes, fn(ClassPopo $class) => stripos($class->name, $className) !== false);
    }

    public function filterByMethodName(string $methodName): array
    {
        return array_filter($this->classes, fn(ClassPopo $class) =>
        array_filter($class->getMethods(), fn($method) => stripos($method->getName(), $methodName) !== false)
        );
    }

    public function filterByPropertyName(string $propertyName): array
    {
        return array_filter($this->classes, fn(ClassPopo $class) =>
        array_filter($class->getProperties(), fn($property) => stripos($property->getName(), $propertyName) !== false)
        );
    }

    public function printSummary(): void
    {
        foreach ($this->classes as $class) {
            echo "Class: {$class->name}\n";
            echo "Methods: " . implode(', ', array_map(fn($m) => $m->getName(), $class->getMethods())) . "\n";
            echo "Properties: " . implode(', ', array_map(fn($p) => $p->getName(), $class->getProperties())) . "\n";
        }
    }
}
