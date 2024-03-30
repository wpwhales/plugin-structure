<?php

namespace Tests\Routing\Middlewares;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Routing\TraitRoutingHandler;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;



/**
 *  !!!! ONLY FOR AJAX CALL !!!!
 *
 * Tests for the Ajax calls to save and get sos stats.
 * For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 * Ajax tests are not marked risky when run in separate processes and wp_debug
 * disabled. But, this makes tests slow so non ajax calls are kept separate
 *
 *
 */
class AjaxRequestTest extends \WP_Ajax_UnitTestCase
{


    public function test_not_logged_in_user_check_middleware()
    {

        $this->app->router->get("/middleware_auth_check", [
            "middleware" => [TestMiddleware::class],
            "uses"       => TestController::class . "@middleware_check"
        ]);


        $response = $this->adminAjaxCall("GET", "/middleware_auth_check");
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

        $response = $this->adminAjaxCall("GET", "/middleware_auth_check");

        $response->assertStatus(200);
        $response->assertContent("$user");


    }

    public function test_verify_csrf_token_middleware_bypass_urls(){
        $this->app->router->post("/csrf_route", [
            "middleware" => [TestCSRFMiddleware::class],
            function(){

                return 123;
            }
        ]);
        $this->app->router->post("/csrf_bypass_route", [
            "middleware" => [TestCSRFMiddleware::class],
            function(){

                return 123;
            }
        ]);
        $this->app->bindingResolver->bind('user_id', function ($val) {
            return $val;
        });
        $this->app->router->post("/csrf_bypass_route/{user_id}/123", [
            "middleware" => [TestCSRFMiddleware::class],
            function(){

                return 123;
            }
        ]);
        $response = $this->adminAjaxCall("POST", "/csrf_route");


        $response->assertStatus(419);


        $response = $this->adminAjaxCall("POST", "/csrf_bypass_route");


        $response->assertStatus(200);

        $response = $this->adminAjaxCall("POST", "/csrf_bypass_route/1/123");


        $response->assertStatus(200);

    }


}

