<?php

namespace WPWCore\View\Engines;

use WPWCore\Filesystem\Filesystem;
use WPWhales\Contracts\View\Engine;

class FileEngine implements Engine
{
    /**
     * The filesystem instance.
     *
     * @var \WPWCore\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file engine instance.
     *
     * @param  \WPWCore\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return $this->files->get($path);
    }
}
