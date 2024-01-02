<?php

namespace WPWhales\Contracts\Redis;

interface Factory
{
    /**
     * Get a Redis connection by name.
     *
     * @param  string|null  $name
     * @return \WPWhales\Redis\Connections\Connection
     */
    public function connection($name = null);
}
