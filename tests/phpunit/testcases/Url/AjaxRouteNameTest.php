<?php

namespace Tests\Url;


use WPWCore\Http\Request;
use WPWhales\Testing\TestResponse;

class AjaxRouteNameTest extends \WP_Ajax_UnitTestCase
{


    public function test_ajax_and_web_routes_name_together()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");
        $this->app->createWebRoutesFromFile(__DIR__ . "/routes/web.php");

        $this->assertEquals($this->app["url"]->route("test_web_route"), site_url("/test_web_route","https"));

        $this->assertEquals($this->app["url"]->adminAjaxRoute("ajax_route_name"), admin_url("admin-ajax.php?action=wpwhales&route=" . urlencode("/test_ajax_route"),"https"));

        $this->assertArrayNotHasKey("test_web_route",$this->app->router->namedRoutes);
    }




}
