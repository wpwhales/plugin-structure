<?php

namespace Tests\Routing;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWCore\Routing\Controller;
use WPWhales\Http\Request;
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


    public function test_routes_plain_response()
    {


        $response = $this->call("GET", "/test_plain_response");


        $response->assertStatus(200);
        $response->assertContent("test_plain_response");
        $response = $this->call("POST", "/test_plain_response");

        $response->assertStatus(200);
        $response->assertContent("test_plain_response");

    }

    public function test_not_existing_routes()
    {


        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", "/NOT_EXIST");

        $this->assertNull($response->baseResponse);

    }


    public function test_routes_json_response()
    {


        $response = $this->call("GET", "/test_json_response");

        $response->assertStatus(200);
        $response->assertJson(["test_json_response"]);
        $response = $this->call("POST", "/test_json_response");

        $response->assertStatus(200);
        $response->assertJson(["test_json_response"]);
    }


    public function test_routes_response_download_output_file()
    {


        $response = $this->call("GET", "/test_csv_content");

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=tasks_web.csv');


    }


    public function test_response_download_already_saved_file()
    {


        $response = $this->call("GET", "/test_binary_file_response");

        $response->assertStatus(200);
        $this->assertEquals("download-file-web.txt", $response->getFile()->getFilename());
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=download-file-web.txt');
        $this->assertEquals("something unique test in web route file", $response->getFile()->getContent());

    }


    public function test_throw_exceptions_in_routes()
    {

        $response = $this->call("GET", "/test_421_response");
        $this->assertInstanceOf(WPWException::class, $response->exception);
        $this->assertEquals("error in web route", $response->exception->getMessage());
        $this->assertEquals(421, $response->exception->getStatusCode());

        $response = $this->call("GET", "/test_500_response");
        $this->assertInstanceOf(WPWException::class, $response->exception);
        $this->assertEquals("error in web route", $response->exception->getMessage());
        $this->assertEquals(500, $response->exception->getStatusCode());


    }


    public function test_no_route_found_parameters_in_public_routing_of_wordpress()
    {
        $response = $this->call("GET", "/something");

        $this->assertEquals("", $this->_last_response);
        $this->assertNull($response->baseResponse);
    }


    public function test_current_logged_in_user_is_accessible_in_routes()
    {
        $user = $this->factory()->user->create();
        wp_set_current_user($user);
        $response = $this->call("GET", "/current_logged_in_user");

        $response->assertJson(["ID" => $user]);
        $response->assertOk();
    }


    public function test_controller_routing_integration()
    {


        $this->app->router->get("/controller_integration", [
            "uses" => ControllerIntegrationWebTest::class . "@index"
        ]);

        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", "/controller_integration", ["test_input" => 1234]);

        $response->assertContent("1234");
    }
}

class ControllerIntegrationWebTest extends Controller
{

    public function index(Request $request)
    {

        $test_input = $request->input("test_input");
        return $test_input;
    }
}

