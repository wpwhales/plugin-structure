<?php

namespace Tests\Url;


use WPWCore\Http\Request;
use WPWhales\Testing\TestResponse;

class RouteNameTest extends \WP_UnitTestCase
{


    public function test_web_route_name()
    {
        $this->app->router->get("/something", [
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
        $this->app->webRouter->get("/something", [
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter

        $this->assertEquals($this->app["url"]->route("something_for_route_name"), site_url("/something","https"));
    }


    public function test_ajax_route_name()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");


        $this->assertEquals($this->app["url"]->adminAjaxRoute("ajax_route_name"), admin_url("admin-ajax.php?action=wpwhales&route=" . urlencode("/test_ajax_route"),"https"));

    }

    public function test_ajax_and_web_routes_name_together()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");
        $this->app->createWebRoutesFromFile(__DIR__ . "/routes/web.php");

        $this->assertEquals($this->app["url"]->route("test_web_route"), site_url("/test_web_route","https"));

        $this->assertEquals($this->app["url"]->adminAjaxRoute("ajax_route_name"), admin_url("admin-ajax.php?action=wpwhales&route=" . urlencode("/test_ajax_route"),"https"));

    }

    public function test_web_route_name_and_assert_response()
    {
        $this->app->router->get("/something", [
            "as" => "something_for_route_name", function () {

                return "123";
            }
        ]);

        $this->app->webRouter->get("/something", [
            "as" => "something_for_route_name", function () {

                return "123";
            }
        ]);
        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter


        $this->assertEquals($this->app["url"]->route("something_for_route_name"), site_url("/something","https"));

        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", $this->app["url"]->route("something_for_route_name"));

        $response->assertContent("123");
    }


    public function test_web_route_name_with_parameters()
    {

        $this->app->router->get("/something", [
            "as" => "something_for_route_name", function () {

                return "123";
            }
        ]);        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter
        $this->app->webRouter->get("/something", [
            "as" => "something_for_route_name", function () {

                return "123";
            }
        ]);

        $this->assertEquals($this->app["url"]->route("something_for_route_name", ["x" => 1]), site_url("/something?x=1","https"));
    }


    public function test_ajax_route_name_with_simple_route_binding()
    {

        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");


        $this->assertEquals($this->app["url"]->adminAjaxRoute("test_ajax_route_binding", ["event" => 1234]), admin_url("admin-ajax.php?action=wpwhales&route=" . urlencode("/test_ajax_route_binding/1234/edit"),"https"));


    }




}
