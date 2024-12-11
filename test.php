<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use quintenmbusiness\PhpAstToolkit\Core\ClassFilter;
use quintenmbusiness\PhpAstToolkit\Core\DirectoryScanner;
use quintenmbusiness\PhpAstToolkit\Service\ClassService;

$path = 'C:\Users\denni\Desktop\projects\packages\PhpAstToolkit\test_folder';

try {
    $scanner = new DirectoryScanner();
    $classes = $scanner->scan($path, true); // Recursive scan
    $filter = new ClassFilter($classes);
    $service = new ClassService();

    // Search for the class "BasicExample"
    $class = $filter->searchByClassName('BasicExample')[0];

    $newClassName = 'CopyExample';
    $newInterfaceName = 'CopyExampleInterface';
    $newInterfacePath = $class->getDirectory() . DIRECTORY_SEPARATOR . $newInterfaceName . '.php';

    // Rename the class
    $class->updateName($newClassName);

    // Set the new file name for the renamed class
    $newFileName = $class->getDirectory() . DIRECTORY_SEPARATOR . $newClassName . '.php';

    // Save the renamed class
    $copy = $service->copy($class, $newFileName);

    // Update a method name in the copied class
    $copy->methods[0]->updateName('NewMethodName');
    $service->save($copy);

    // Create an interface version of the class
    $service->createInterfaceVersion(
        classPopo: $copy,
        newName: $newInterfaceName,
        newPath: $newInterfacePath,
        newExtends: null,
        newImplements: [],
        updateOriginal: true
    );

    echo "Class renamed to {$newClassName} and saved as {$newFileName}.\n";
    echo "Interface created as {$newInterfaceName} and saved at {$newInterfacePath}.\n";

    // List all methods in the modified class
    echo "Modified class methods:\n";
    foreach ($copy->methods as $method) {
        echo " - {$method->name} (Visibility: {$method->visibility})\n";
    }

} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
