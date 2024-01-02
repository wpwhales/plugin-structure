<?php

namespace WPWhales\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
