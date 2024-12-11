<?php

namespace test_classes;

/**
 * Class BasicExample
 *
 * A basic example class to demonstrate properties and methods.
 */
class OtherExample
{
    /**
     * A public property.
     *
     * @var mixed
     */
    public int $publicIntProperty = 5;

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

    /**
     * A public method.
     *
     * @return string
     */
    public function publicMethod(): string
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

    public function someMethod(): void {

    }
}

/**
 * A global helper function.
 *
 * @return string
 */
function globalHelperFunction(): string
{
    return 'This is a global helper function.';
}