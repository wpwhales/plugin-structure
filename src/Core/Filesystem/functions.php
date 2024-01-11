<?php

namespace WPWhales\Filesystem;

function join_paths($basePath, ...$paths)
{
    foreach ($paths as $index => $path) {
        if (empty($path)) {
            unset($paths[$index]);
        } else {
            $paths[$index] = DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
        }
    }

    return $basePath.implode('', $paths);
}