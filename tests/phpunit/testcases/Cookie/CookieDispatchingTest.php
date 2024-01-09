<?php

namespace Tests\Cookie;


use Symfony\Component\HttpFoundation\Cookie;
use Tests\Routing\ControllerIntegrationWebTest;
use WPWCore\Cookie\CookieJar;
use WPWhales\Testing\TestResponse;

class CookieDispatchingTest extends \WP_UnitTestCase
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

        $response->assertCookie("test_cookie","test_value",\WPWCore\config("session.encrypt"));

    }

    public function test_cookies_get_sent_in_template_redirect_hook()
    {

        global $wp_filter;

        $cookie = (new CookieJar())->make("test_cookie", "test_value", 60);

        $this->assertInstanceOf(Cookie::class, $cookie);


        $path = $cookie->getPath();
        $domain = $cookie->getDomain();
        $secure = $cookie->isSecure();
        $httponly = $cookie->isHttpOnly();
        $samesite = $cookie->getSameSite();

        $cookieInstance = $this->createPartialMock(CookieJar::class, ["setCookie", "getQueuedCookies"]);
        $cookieInstance->expects($this->once())->method("getQueuedCookies")->will($this->returnValue([$cookie]));


        if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
            $cookieInstance->expects($this->atLeastOnce())->method("setCookie")->with(
                "test_cookie", "test_value", [
                "expires"  => time() + 60 * 60,
                "path"     => $path,
                "domain"   => $domain,
                "secure"   => $secure,
                "httponly" => $httponly,
                "samesite" => $samesite
            ]);


        } else {
            $cookieInstance->expects($this->atLeastOnce())->method("setCookie")->with("test_cookie", "test_value", time() + 60 * 60, $path, $domain, $secure, $httponly);


        }


        $this->app["cookie"] = $cookieInstance;


        foreach ($wp_filter["template_redirect"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "sendCookieHeaders")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }

        }


    }

}