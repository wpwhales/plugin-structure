<?php

namespace Tests\Cookie;


use Symfony\Component\HttpFoundation\Cookie;
use Tests\Routing\ControllerIntegrationWebTest;
use WPWCore\Cookie\CookieJar;
use WPWhales\Testing\TestResponse;

class CookieAttachedInWebResponseTest extends \WP_UnitTestCase
{

    public function test_cookies_are_attached_in_response()
    {
        $app = $this->app;
        $this->app->router->get("/cookie_test",[
            function() use($app){
            \WPWCore\app("cookie")->queue("test_cookie","test_value");

            return 123;
            }
        ]);

        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", "/cookie_test");

        $response->assertCookie("test_cookie","test_value",false);

    }



}