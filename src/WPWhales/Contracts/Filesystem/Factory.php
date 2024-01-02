<?php

namespace WPWhales\Contracts\Filesystem;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
