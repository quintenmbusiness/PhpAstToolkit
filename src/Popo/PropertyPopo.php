<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use PhpParser\Comment\Doc;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;

class PropertyPopo extends BasePopo
{
    public function __construct(
        string $name,
        string $visibility,
        public ?string $type,
        Property $astNode
    ) {
        parent::__construct($name, $astNode, null, $visibility);
    }

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

        $this->astNode->type = $newType !== null ? new Identifier($newType) : null;
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
            $prop->default = match (true) {
                is_int($defaultValue) => new LNumber($defaultValue),
                is_float($defaultValue) => new DNumber($defaultValue),
                is_string($defaultValue) => new String_($defaultValue),
                is_bool($defaultValue) => new Expr\ConstFetch(
                    new Name($defaultValue ? 'true' : 'false')
                ),
                null === $defaultValue => null,
                is_null($defaultValue) => new Expr\ConstFetch(
                    new Name('null')
                ),
                default => throw new \InvalidArgumentException("Unsupported default value type: " . gettype($defaultValue)),
            };
        }
    }
}
