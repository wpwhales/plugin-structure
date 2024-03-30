<?php

namespace Tests\Routing\Middlewares;


use WPWCore\Routing\Middleware\VerifyCsrfToken;
use WPWCore\Session\TokenMismatchException;
use WPWhales\Http\Request;

class TestCSRFMiddleware extends VerifyCsrfToken
{


    protected $except = ["csrf_bypass_route","csrf_bypass_route/*/123"];


    /**
     * Handle an incoming request.
     *
     * @param  \WPWCore\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {


        if (
            $this->isReading($request) ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return $next($request);
        }

        throw new TokenMismatchException('CSRF token mismatch.');
    }


    protected function inExceptArray($request)
    {


        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

}