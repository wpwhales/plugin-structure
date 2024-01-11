<?php
namespace Tests\Router;

use PHPUnit\Framework\TestCase;


class RouteBinderIntegrationTest extends \WP_UnitTestCase
{
    public function test_route_binding_in_application_dispatcher()
    {


        // Register a simple binding
        $this->app->bindingResolver->bind('wildcard', function ($val) {
            return "{$val} Resolved";
        });



        // Register a route with a wildcard
        $this->app->router->get('/123/{wildcard}', [function ($wildcard) {
            return \WPWCore\response($wildcard);
        }]);


        // Dispatch the request
        $response = $this->call("GET","/123/sample-test-binding");

        // Assert the binding is resolved
        $this->assertSame('sample-test-binding Resolved', $response->getContent(), '-> Response should be the wildcard value after been resolved!');
    }
}