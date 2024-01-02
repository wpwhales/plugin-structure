<?php

namespace WPWhales\Http\Client\Events;

use WPWhales\Http\Client\Request;

class ConnectionFailed
{
    /**
     * The request instance.
     *
     * @var \WPWhales\Http\Client\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Http\Client\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
