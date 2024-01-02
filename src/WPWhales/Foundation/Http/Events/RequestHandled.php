<?php

namespace WPWhales\Foundation\Http\Events;

class RequestHandled
{
    /**
     * The request instance.
     *
     * @var \WPWhales\Http\Request
     */
    public $request;

    /**
     * The response instance.
     *
     * @var \WPWhales\Http\Response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Http\Request  $request
     * @param  \WPWhales\Http\Response  $response
     * @return void
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
