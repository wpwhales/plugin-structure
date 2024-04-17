<?php

namespace Tests\Url;


use Carbon\Carbon;
use WPWCore\Http\Request;
use WPWCore\Routing\Middleware\ValidateSignature;
use WPWhales\Testing\TestResponse;

class SignedWebRoutesTest extends \WP_UnitTestCase
{


    public function test_web_route_work_with_signed_route_name()
    {
        $this->app->router->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
        $this->app->webRouter->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter


        $response = $this->call("GET", $this->app["url"]->signedRoute("something_for_route_name", ["x" => 1], Carbon::now()->addMonth()));
        $response->assertOk();



    }


    public function test_web_route_fail_with_malformed_signed_route_using_parameters()
    {
        $this->app->router->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter
        $this->app->webRouter->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);

        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", $this->app["url"]->signedRoute("something_for_route_name", ["x" => 1], Carbon::now()->addMonth()),["xyz"=>123]);
        $response->assertStatus(403);
        $this->assertStringContainsString("Invalid signature",$response->getContent());

    }


    public function test_web_route_fail_with_malformed_signed_route_using_expiry()
    {
        $this->app->router->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);

        //duplicate for naming  as name url generation is dependent on webRouter ro adminAjaxRouter
        $this->app->webRouter->get("/something", [
            "middleware"=>[ValidateSignature::class],
            "as" => "something_for_route_name", function () {

                return 123;
            }
        ]);
            $today = Carbon::now();
            $today_add_15_min = $today->addMinutes(15);


        Carbon::setTestNow(Carbon::now()->addMinutes(14));

        $response = $this->call("GET", $this->app["url"]->signedRoute("something_for_route_name", ["x" => 1],$today_add_15_min ));
        $response->assertStatus(200);
        $response->assertContent("123");

        Carbon::setTestNow(Carbon::now()->addMinutes(15));

        $response = $this->call("GET", $this->app["url"]->signedRoute("something_for_route_name", ["x" => 1],$today_add_15_min ));
        $response->assertStatus(403);
        $this->assertStringContainsString("Invalid signature",$response->getContent());

        Carbon::setTestNow(Carbon::now()->addMinutes(16));

        $response = $this->call("GET", $this->app["url"]->signedRoute("something_for_route_name", ["x" => 1],$today_add_15_min ));
        $response->assertStatus(403);

        $this->assertStringContainsString("Invalid signature",$response->getContent());



    }






}
