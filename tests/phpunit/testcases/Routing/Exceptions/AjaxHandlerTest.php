<?php

namespace Tests\Routing\Exceptions;

use WPWCore\Routing\Controller;
use WPWhales\Http\Request;
use WPWhales\Support\Facades\Config;


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
class AjaxHandlerTest extends \WP_Ajax_UnitTestCase
{

    public function createApplication()
    {


        $app = new \WPWCore\Application(
            dirname(__DIR__)
        );
        $app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");


        $app->singleton(\WPWhales\Contracts\Debug\ExceptionHandler::class, \WPWCore\Exceptions\Handler::class);

        $app->withFacades();
        $app->withEloquent();

        return $app;
    }


    public function test_exception_in_route_contains_the_log_if_debug_is_true_else_no_log()
    {

        Config::set("app.debug", true);
        $response = $this->adminAjaxCall("GET", "/test_421_response");


        $response->assertStatus(421);

        $response->assertJsonStructure([
            "message",
            "file",
            "line",
            "exception"
        ]);

        Config::set("app.debug", false);
        $response = $this->adminAjaxCall("GET", "/test_421_response");

        $response->assertStatus(421);
        $response->assertJsonStructure([
            "message",

        ]);
        $response->assertJsonMissing([
            "file",
            "line",
            "exception"
        ]);

    }


}


