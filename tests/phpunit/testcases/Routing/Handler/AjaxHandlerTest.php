<?php

namespace Tests\Routing;

use WPWCore\Routing\Controller;
use WPWhales\Http\Request;


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



    public function test_routes_plain_response()
    {


        $response = $this->adminAjaxCall("GET", "/test_plain_response");



        $response->assertStatus(200);
        $response->assertContent("1");
        $response = $this->adminAjaxCall("POST", "/test_plain_response");

        $response->assertStatus(200);
        $response->assertContent("2");

    }

    public function test_routes_json_response()
    {


        $response = $this->adminAjaxCall("GET", "/test_json_response");

        $response->assertStatus(200);
        $response->assertJson([1, 2, 3]);
        $response = $this->adminAjaxCall("POST", "/test_json_response");

        $response->assertStatus(200);
        $response->assertJson([4, 5, 6]);
    }


    public function test_routes_response_download_output_file()
    {


        $response = $this->adminAjaxCall("GET", "/test_csv_content");

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=tasks.csv');


    }


    public function test_response_download_already_saved_file()
    {


        $response = $this->adminAjaxCall("GET", "/test_binary_file_response");

        $response->assertStatus(200);
        $this->assertEquals("download-file-ajax.txt", $response->getFile()->getFilename());
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=download-file-ajax.txt');
        $this->assertEquals("something unique test", $response->getFile()->getContent());

    }


    public function test_throw_exceptions_in_routes()
    {


        $response = $this->adminAjaxCall("GET", "/test_421_response");
        $response->assertJsonFragment(["message" => "error in route"]);
        $response->assertStatus(421);

        $response = $this->adminAjaxCall("GET", "/test_500_response");
        $response->assertJsonFragment(["message" => "error in route"]);
        $response->assertStatus(500);
    }


    public function test_no_query_parameters_in_admin_ajax_file()
    {
        $response = $this->adminAjaxCall("GET", "/something", ["NOPARAM" => true]);
        $this->assertEquals("", $this->_last_response);
    }


    public function test_no_route_found_parameters_in_admin_ajax()
    {


        $response = $this->call("GET", "/something");

        $this->assertEquals("", $this->_last_response);
        $this->assertNull($response->baseResponse);
    }


    public function test_current_logged_in_user_is_accessible_in_routes()
    {
        $user = $this->factory()->user->create();
        wp_set_current_user($user);
        $response = $this->adminAjaxCall("GET", "/current_logged_in_user");

        $response->assertJson(["ID" => $user]);
        $response->assertOk();
    }


    public function test_controller_routing_integration()
    {


        $this->app->router->get("/controller_integration",[
            "uses"=>ControllerIntegrationAjaxTest::class."@index"
        ]);


        $response = $this->adminAjaxCall("GET", "/controller_integration",["test_input"=>1234]);

        $response->assertContent("1234");
    }





}




class ControllerIntegrationAjaxTest extends Controller{

    public function index(Request $request){

        $test_input = $request->input("test_input");
        return $test_input;
    }
}


