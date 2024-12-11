<?php

/**
 * Class BasicExample
 *
 * A basic example class to demonstrate properties and methods.
 */
class CopyExample implements CopyExampleInterface
{
    /**
     * @inheritdoc
     */
    public function NewMethodName(): string
    {
        return 'This is a public method.';
    }
    /**
     * A protected method.
     *
     * @return string
     */
    protected function protectedMethod(): string
    {
        return 'This is a protected method.';
    }
    /**
     * A private method.
     *
     * @return string
     */
    private function privateMethod(): string
    {
        return 'This is a private method.';
    }
    /**
     * A public property.
     *
     * @var mixed
     */
    public $publicProperty;
    /**
     * A protected property.
     *
     * @var mixed
     */
    protected $protectedProperty;
    /**
     * A private property.
     *
     * @var mixed
     */
    private $privateProperty;
}