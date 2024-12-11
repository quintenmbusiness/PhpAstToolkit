<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

class PropertyPopo
{
    /**
     * @param string $name Name of the property.
     * @param string $visibility Visibility of the property (public, protected, private).
     * @param string|null $type The type of the property.
     * @param Property $astNode The raw AST node for the property.
     */
    public function __construct(
        public string $name,
        public string $visibility,
        public ?string $type,
        public Property $astNode
    ) {}

    /**
     * Update the name of the property.
     *
     * @param string $newName
     * @return void
     */
    public function updateName(string $newName): void
    {
        $this->name = $newName;

        foreach ($this->astNode->props as $prop) {
            if ($prop instanceof PropertyProperty) {
                $prop->name->name = $newName;
            }
        }
    }

    /**
     * Update the visibility and modifiers of the property.
     *
     * @param string $newVisibility Can include modifiers such as 'public', 'protected', 'private', 'static', 'readonly'.
     * @return void
     */
    public function updateVisibility(string $newVisibility): void
    {
        $this->visibility = $newVisibility;

        $modifiers = explode(' ', $newVisibility);

        $flags = 0;

        foreach ($modifiers as $modifier) {
            $flags |= match ($modifier) {
                'public' => Class_::MODIFIER_PUBLIC,
                'protected' => Class_::MODIFIER_PROTECTED,
                'private' => Class_::MODIFIER_PRIVATE,
                'static' => Class_::MODIFIER_STATIC,
                'readonly' => Class_::MODIFIER_READONLY,
                default => throw new \InvalidArgumentException("Invalid modifier: $modifier"),
            };
        }

        $this->astNode->flags = $flags;
    }

    /**
     * Update the type of the property.
     *
     * @param string|null $newType
     * @return void
     */
    public function updateType(?string $newType): void
    {
        $this->type = $newType;

        $this->astNode->type = $newType !== null ? new \PhpParser\Node\Identifier($newType) : null;
    }

    /**
     * Get the doc comment for the property.
     *
     * @return string|null
     */
    public function getDocComment(): ?string
    {
        return $this->astNode->getDocComment()?->getText();
    }

    /**
     * Update the doc comment for the property.
     *
     * @param string|null $docComment
     * @return void
     */
    public function updateDocComment(?string $docComment): void
    {
        $this->astNode->setDocComment(
            $docComment !== null ? new \PhpParser\Comment\Doc($docComment) : null
        );
    }

    /**
     * Get the default value of the property, if any.
     *
     * @return mixed|null
     */
    public function getDefaultValue(): mixed
    {
        foreach ($this->astNode->props as $prop) {
            if ($prop instanceof PropertyProperty && $prop->default !== null) {
                return $prop->default->value ?? null;
            }
        }

        return null;
    }

    /**
     * Update the default value of the property.
     *
     * @param mixed $defaultValue
     * @return void
     */
    public function updateDefaultValue(mixed $defaultValue): void
    {
        foreach ($this->astNode->props as $prop) {
            if ($defaultValue !== null) {
                $prop->default = match (true) {
                    is_int($defaultValue) => new LNumber($defaultValue),
                    is_float($defaultValue) => new DNumber($defaultValue),
                    is_string($defaultValue) => new String_($defaultValue),
                    is_bool($defaultValue) => new Expr\ConstFetch(
                        new Name($defaultValue ? 'true' : 'false')
                    ),
                    is_null($defaultValue) => new Expr\ConstFetch(
                        new Name('null')
                    ),
                    default => throw new \InvalidArgumentException("Unsupported default value type: " . gettype($defaultValue)),
                };
            } else {
                $prop->default = null; // Clear the default value
            }
        }
    }
}
