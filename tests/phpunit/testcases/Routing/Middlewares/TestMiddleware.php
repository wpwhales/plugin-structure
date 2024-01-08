<?php

namespace Tests\Routing\Middlewares;


use WPWhales\Http\Request;

class TestMiddleware
{




    /**
     * Handle an incoming request.
     *
     * @param  \WPWCore\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(\WPWCore\Http\Request $request, \Closure $next)
    {


        if(!is_user_logged_in()){
            return \WPWCore\response('Unauthorized', 401);
        }
        return $next($request);
    }


}