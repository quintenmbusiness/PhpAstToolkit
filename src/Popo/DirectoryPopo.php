<?php

namespace quintenmbusiness\PhpAstToolkit\Popo;

use Illuminate\Support\Collection;

class DirectoryPopo
{
    /**
     * @param string $name
     * @param string $absolutePath
     * @param Collection<ClassPopo> $classes
     * @param Collection<DirectoryPopo> $subdirectories
     */
    public function __construct(
        public string $name,
        public string $absolutePath,
        public Collection $classes,
        public Collection $subdirectories
    ) {}
}