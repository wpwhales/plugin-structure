<?php

namespace WPWCore\Auth\Events;

use WPWhales\Http\Request;

class Lockout
{
    /**
     * The throttled request.
     *
     * @var \WPWhales\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
