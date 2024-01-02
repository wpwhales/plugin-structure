<?php

namespace WPWhales\Contracts\Cache;

interface Factory
{
    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Cache\Repository
     */
    public function store($name = null);
}
