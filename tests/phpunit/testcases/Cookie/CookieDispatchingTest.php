<?php

namespace Tests\Cookie;


use Symfony\Component\HttpFoundation\Cookie;
use Tests\Routing\ControllerIntegrationWebTest;
use WPWCore\Cookie\CookieJar;
use WPWhales\Testing\TestResponse;

class CookieDispatchingTest extends \WP_UnitTestCase
{


    public function test_cookies_get_sent_in_init_and_template_redirect_hook()
    {

        global $wp_filter;


        $cookieInstance = $this->createPartialMock(CookieJar::class, ["setCookie"]);


        $cookieInstance->queue("test_cookie", "test_value", 60);

        $cookie = $cookieInstance->queued("test_cookie");

        $path = $cookie->getPath();
        $domain = $cookie->getDomain();
        $secure = $cookie->isSecure();
        $httponly = $cookie->isHttpOnly();
        $samesite = $cookie->getSameSite();

        if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
            $cookieInstance->expects($this->atLeastOnce())->method("setCookie")->with(
                "test_cookie", "test_value", [
                "expires"  => $cookie->getExpiresTime(),
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


        foreach ($wp_filter["init"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "sendCookieHeaders")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }

        }

        foreach ($wp_filter["template_redirect"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "sendCookieHeaders")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }

        }


    }

}