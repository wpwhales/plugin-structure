<?php

namespace Tests\Routing;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Routing\Middlewares\TestController;
use Tests\Routing\Middlewares\TestMiddleware;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWCore\Routing\Controller;
use WPWhales\Http\Request;
use WPWhales\Testing\TestResponse;


/**
 *  !!!! ONLY FOR WEB CALL !!!!
 *
 * Tests for the Ajax calls to save and get sos stats.
 * For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 * Ajax tests are not marked risky when run in separate processes and wp_debug
 * disabled. But, this makes tests slow so non ajax calls are kept separate
 *
 *
 */
class WebRequestTest extends \WP_UnitTestCase
{


    public function test_not_logged_in_user_check_middleware()
    {

        $this->app->router->get("/middleware_auth_check", [
            "middleware" => [TestMiddleware::class],
            "uses"       => TestController::class . "@middleware_check"
        ]);


        $response = $this->call("GET", "/middleware_auth_check");
        $response->assertStatus(401);
        $response->assertContent("Unauthorized");


    }

    public function test_logged_in_user_check_middleware()
    {

        $this->app->router->get("/middleware_auth_check", [
            "middleware" => [TestMiddleware::class],
            "uses"       => TestController::class . "@middleware_check"
        ]);

        $user = $this->factory()->user->create();
        wp_set_current_user($user);

        $response = $this->call("GET", "/middleware_auth_check");

        $response->assertStatus(200);
        $response->assertContent("$user");


    }



}


