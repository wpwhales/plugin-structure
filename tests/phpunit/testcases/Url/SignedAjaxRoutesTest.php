<?php

namespace Tests\Url;


use Carbon\Carbon;
use WPWCore\Http\Request;
use WPWCore\Routing\Middleware\ValidateSignature;
use WPWhales\Testing\TestResponse;

class SignedAjaxRoutesTest extends \WP_Ajax_UnitTestCase
{


    public function test_ajax_signed_route_structure()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");

        $expiry = Carbon::now()->addMonth();
        $signed_route = $this->app["url"]->signedAdminAjaxRoute("ajax_route_name", ["x" => 1], $expiry);
        $parse_url  =parse_url($signed_route);
        parse_str($parse_url["query"],$queryStringArray);
        $this->assertArrayHasKey("expires",$queryStringArray);
        $this->assertArrayHasKey("signature",$queryStringArray);
        $this->assertArrayHasKey("x",$queryStringArray);
        $this->assertArrayHasKey("action",$queryStringArray);
        $this->assertArrayHasKey("route",$queryStringArray);

        $this->assertEquals($queryStringArray["expires"],$expiry->getTimestamp());
        $this->assertEquals($queryStringArray["x"],"1");
        $this->assertEquals($queryStringArray["action"],"wpwhales");
        $this->assertEquals($queryStringArray["route"],"/test_ajax_route");

    }


    public function test_ajax_route_work_with_signed_route_name()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");

        $url = $this->app["url"]->signedAdminAjaxRoute("ajax_signed_route_name", ["x" => 1], Carbon::now()->addMonth());


        $response = $this->adminAjaxCall("GET",$url );
        $response->assertOk();



    }


    public function test_web_route_fail_with_malformed_signed_route_using_parameters()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");


        $url = $this->app["url"]->signedAdminAjaxRoute("ajax_signed_route_name", ["x" => 1], Carbon::now()->addMonth());
        $url.="&y=1";

        $response = $this->adminAjaxCall("GET",$url );

        $response->assertStatus(403);
        $this->assertStringContainsString("Invalid signature",$response->getContent());

    }


    public function test_web_route_fail_with_malformed_signed_route_using_expiry()
    {
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");



        $today = Carbon::now();
        $today_add_15_min = $today->addMinutes(15);



        Carbon::setTestNow(Carbon::now()->addMinutes(14));

        $response = $this->adminAjaxCall("GET",$this->getUrlForTime("ajax_signed_route_name",$today_add_15_min) );
        $response->assertStatus(200);
        $response->assertContent("123");

        Carbon::setTestNow(Carbon::now()->addMinutes(15));

        $response = $this->adminAjaxCall("GET", $this->getUrlForTime("ajax_signed_route_name",$today_add_15_min));
        $response->assertStatus(403);
        $this->assertStringContainsString("Invalid signature",$response->getContent());

        Carbon::setTestNow(Carbon::now()->addMinutes(16));

        $response = $this->adminAjaxCall("GET", $this->getUrlForTime("ajax_signed_route_name",$today_add_15_min));
        $response->assertStatus(403);

        $this->assertStringContainsString("Invalid signature",$response->getContent());



    }


    private function getUrlForTime($routeName,$time){
        $url = $this->app["url"]->signedAdminAjaxRoute($routeName, ["x" => 1],$time );
        return $url;
    }





}
