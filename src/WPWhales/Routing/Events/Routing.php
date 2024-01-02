<?php

namespace WPWhales\Routing\Events;

class Routing
{
    /**
     * The request instance.
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
    public function __construct($request)
    {
        $this->request = $request;
    }
}
