<?php

namespace WPWhales\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \WPWhales\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
