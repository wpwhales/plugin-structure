<?php

namespace Tests\Routing\Exceptions;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWCore\Routing\Controller;
use WPWhales\Http\Request;
use WPWhales\Support\Facades\Config;
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
class WebHandlerTest extends \WP_UnitTestCase
{


    public function set_up()
    {
        parent::set_up(); // TODO: Change the autogenerated stub


        $this->app->createWebRoutesFromFile(__DIR__ . "/routes/web.php");
    }

    public function test_exception_in_route_contains_the_log_if_debug_is_true_else_no_log()
    {

        Config::set("app.debug",true);
        $response = $this->call("GET", "/test_421_response");

        $response->assertStatus(421);
        $this->assertStringContainsString("error in web route (500 Internal Server Error)",$response->content());


        Config::set("app.debug",false);
        $response = $this->call("GET", "/test_421_response");

        $response->assertStatus(421);
        $this->assertStringContainsString('<div class="error-code">421</div>',$response->content());
        $this->assertStringContainsString('<div class="error-message">Misguided Request</div>',$response->content());



    }

    public function test_exception_view_change()
    {

        Config::set("app.debug",true);
        $response = $this->call("GET", "/test_421_response");


        $response->assertStatus(421);
        $this->assertStringContainsString("error in web route (500 Internal Server Error)",$response->content());

        $paths = \WPWCore\config("view.paths");
        array_unshift($paths,__DIR__);

        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);

        Config::set("app.debug",false);
        $response = $this->call("GET", "/test_421_response");


        $response->assertStatus(421);

        $response->assertContent("UPDATED ERROR MESSAGE");


    }



}


