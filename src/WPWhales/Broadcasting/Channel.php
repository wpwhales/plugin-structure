<?php

namespace WPWhales\Broadcasting;

use WPWhales\Contracts\Broadcasting\HasBroadcastChannel;

class Channel
{
    /**
     * The channel's name.
     *
     * @var string
     */
    public $name;

    /**
     * Create a new channel instance.
     *
     * @param  \WPWhales\Contracts\Broadcasting\HasBroadcastChannel|string  $name
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name instanceof HasBroadcastChannel ? $name->broadcastChannel() : $name;
    }

    /**
     * Convert the channel instance to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
