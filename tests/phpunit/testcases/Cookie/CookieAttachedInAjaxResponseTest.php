<?php

namespace Tests\Cookie;


use Symfony\Component\HttpFoundation\Cookie;
use Tests\Routing\ControllerIntegrationWebTest;
use WPWCore\Cookie\CookieJar;
use WPWhales\Testing\TestResponse;

class CookieAttachedInAjaxResponseTest extends \WP_Ajax_UnitTestCase
{

    public function test_cookies_are_attached_in_response()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__."/routes/ajax.php");
        /**
         * @var $response TestResponse
         */
        $response = $this->adminAjaxCall("GET", "/test_ajax_route");

        $response->assertCookie(\WPWCore\config("session.cookie_guest"));

        $response->assertCookie("test_cookie","test_value",false);
    }



}