<?php

namespace WPWhales\Routing\Matching;

use WPWhales\Http\Request;
use WPWhales\Routing\Route;

interface ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \WPWhales\Routing\Route  $route
     * @param  \WPWhales\Http\Request  $request
     * @return bool
     */
    public function matches(Route $route, Request $request);
}
