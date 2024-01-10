<?php

namespace Tests\Routing\View;


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


    public function set_up()
    {
        parent::set_up();
        $paths = \WPWCore\config("view.paths");
        $paths[] = __DIR__;

        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);
    }

    public function test_view_in_response()
    {



        $this->app->router->get("/view_in_response", [
            "uses" => \Tests\Routing\View\TestController::class . "@view_in_response"
        ]);


        $response = $this->call("GET", "/view_in_response");

        $response->assertStatus(200);
        $response->assertContent("<h1>Hello World</h1>");
        $response->assertViewIs("test");


    }


    public function test_view_with_method()
    {



        $this->app->router->get("/view_with_method", [
            "uses" => \Tests\Routing\View\TestController::class . "@view_with_method"
        ]);


        $response = $this->call("GET", "/view_with_method");

        $response->assertStatus(200);
        $response->assertContent("<h1>Hello World</h1>");

        $response->assertViewIs("test");
        $response->assertViewHasAll([
            "data_1" => [1, 2, 3],
            "data_2" => "something"
        ]);



    }



    public function test_view_exception_is_accessible_in_response(){

        $this->app->router->get("/view_error_test", [
            function(){


            \WPWCore\view("error");
            }
        ]);


        $response = $this->call("GET", "/view_error_test");

        $response->assertStatus(500);

    }



}


