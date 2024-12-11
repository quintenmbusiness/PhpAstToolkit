# PhpAstCodeToolkit

PhpAstCodeToolkit is a PHP library that simplifies working with Abstract Syntax Trees (AST) by providing utilities to analyze, modify, and generate PHP code. Built on top of the powerful [nikic/php-parser](https://github.com/nikic/PHP-Parser), it offers a streamlined API for developers.

## Features

- Analyze PHP files and extract class, method, and property information.
- Modify existing code structures using a clean object-oriented approach.
- Generate interfaces and classes programmatically.
- Synchronize changes between the AST and source code.
- Lightweight and easy to integrate into any PHP project.

## Installation

Install the package via Composer:

```bash
composer require quintenmbusiness/php-ast-code-toolkit
```

## Usage

### Analyzing PHP Files

Use the `DirectoryScanner` to analyze PHP files in a directory and extract class information.

```php
use quintenmbusiness\PhpAstCodeGenerationHelper\Core\DirectoryScanner;
use quintenmbusiness\PhpAstCodeGenerationHelper\Core\ClassFilter;

$scanner = new DirectoryScanner();
$classes = $scanner->scan('/path/to/php/files', true);
$filter = new ClassFilter($classes);

// Search for a specific class
$class = $filter->searchByClassName('MyClass')[0];
```

### Modifying a Class

Change the name of a class and update a method:

```php
$class->updateName('NewClassName');
$class->methods[0]->updateName('newMethodName');
$class->save();
```

### Generating an Interface

Create an interface from an existing class:

```php
$newInterfacePath = '/path/to/NewInterface.php';
$class->createInterfaceVersion(
    newName: 'NewInterface',
    newPath: $newInterfacePath,
    newExtends: null,
    newImplements: [],
    updateOriginal: true
);
```

### Copying a Class

Copy a class to a new file:

```php
$newFilePath = '/path/to/NewClass.php';
$newClass = $class->copy($newFilePath);
```

## Requirements

- PHP 8.1 or higher
- [nikic/php-parser](https://github.com/nikic/PHP-Parser) (installed automatically via Composer)

## Credits

This library is powered by [nikic/php-parser](https://github.com/nikic/PHP-Parser).

## License

PhpAstCodeToolkit is open-source software licensed under the MIT License.

