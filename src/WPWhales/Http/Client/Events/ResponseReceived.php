<?php

namespace WPWhales\Http\Client\Events;

use WPWhales\Http\Client\Request;
use WPWhales\Http\Client\Response;

class ResponseReceived
{
    /**
     * The request instance.
     *
     * @var \WPWhales\Http\Client\Request
     */
    public $request;

    /**
     * The response instance.
     *
     * @var \WPWhales\Http\Client\Response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Http\Client\Request  $request
     * @param  \WPWhales\Http\Client\Response  $response
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
