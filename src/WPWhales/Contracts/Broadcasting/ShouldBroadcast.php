<?php

namespace WPWhales\Contracts\Broadcasting;

interface ShouldBroadcast
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \WPWhales\Broadcasting\Channel|\WPWhales\Broadcasting\Channel[]|string[]|string
     */
    public function broadcastOn();
}
