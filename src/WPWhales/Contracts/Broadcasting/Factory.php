<?php

namespace WPWhales\Contracts\Broadcasting;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
