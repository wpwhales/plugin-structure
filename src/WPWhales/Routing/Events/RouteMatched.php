<?php

namespace WPWhales\Routing\Events;

class RouteMatched
{
    /**
     * The route instance.
     *
     * @var \WPWhales\Routing\Route
     */
    public $route;

    /**
     * The request instance.
     *
     * @var \WPWhales\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Routing\Route  $route
     * @param  \WPWhales\Http\Request  $request
     * @return void
     */
    public function __construct($route, $request)
    {
        $this->route = $route;
        $this->request = $request;
    }
}
