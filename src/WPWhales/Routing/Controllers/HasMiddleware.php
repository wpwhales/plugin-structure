<?php

namespace WPWhales\Routing\Controllers;

interface HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return \WPWhales\Routing\Controllers\Middleware|array
     */
    public static function middleware();
}
