<?php

namespace Tests\Url;


use Carbon\Carbon;
use WPWCore\Http\Request;
use WPWhales\Testing\TestResponse;

class SignedRouteNameTest extends \WP_UnitTestCase
{


    public function test_web_route_name()
    {
        $this->app->router->get("/something", [
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);

//        dd($this->app["url"]->signedRoute("something_for_route_name",["x"=>1],Carbon::now()->addMonth()));
    }





}
