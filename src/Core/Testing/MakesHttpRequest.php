<?php

namespace WPWCore\Testing;


use Carbon\Carbon;
use WPWCore\Http\Request;
use WPWhales\Support\Arr;
use WPWhales\Support\Collection;
use WPWhales\Support\Str;
use WPWhales\Testing\Assert as PHPUnit;
use WPWhales\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

trait MakesHttpRequest
{

    /**
     * Last  response set by output buffering. This is set via echo -or- Response  -or- wp_die.
     *
     * @var string
     */
    protected $_last_response = '';

    /**
     * The last response returned by the application.
     *
     * @var \Illuminate\Testing\TestResponse
     */
    protected $response;

    /**
     * The current URI being viewed.
     *
     * @var string
     */
    protected $currentUri;

    /**
     * The base URI
     *
     * @var string
     */
    protected $baseUrl = WP_TESTS_DOMAIN;

    /**
     * Visit the given URI with a JSON request.
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $content = json_encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE'   => 'application/json',
            'Accept'         => 'application/json',
        ], $headers);

        $this->call(
            $method, $uri, [], [], [], $this->transformHeadersToServerVars($headers), $content
        );

        return $this;
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param string $uri
     * @param array $headers
     * @return $this
     */
    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('GET', $uri, [], [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('POST', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('PUT', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function patch($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('PATCH', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('DELETE', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a OPTIONS request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function options($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('OPTIONS', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Visit the given URI with a HEAD request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return $this
     */
    public function head($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        $this->call('HEAD', $uri, $data, [], [], $server);

        return $this;
    }

    /**
     * Send the given request through the application.
     *
     * This method allows you to fully customize the entire Request object.
     *
     * @param \Illuminate\Http\Request $request
     * @return $this
     */
    public function handle(Request $request)
    {
        $this->currentUri = $request->fullUrl();

        $this->response = TestResponse::fromBaseResponse(
            $this->app->prepareResponse($this->app->handle($request))
        );

        return $this;
    }

    /**
     * Assert that the response contains JSON.
     *
     * @param array|null $data
     * @return $this
     */
    protected function shouldReturnJson(array $data = null)
    {
        return $this->receiveJson($data);
    }

    /**
     * Assert that the response contains JSON.
     *
     * @param array|null $data
     * @return $this|null
     */
    protected function receiveJson($data = null)
    {
        return $this->seeJson($data);
    }

    /**
     * Assert that the response contains an exact JSON array.
     *
     * @param array $data
     * @return $this
     */
    public function seeJsonEquals(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            json_decode($this->response->getContent(), true)
        ));

        $data = json_encode(Arr::sortRecursive(
            json_decode(json_encode($data), true)
        ));

        PHPUnit::assertEquals($data, $actual);

        return $this;
    }

    /**
     * Assert that the response contains JSON.
     *
     * @param array|null $data
     * @param bool $negate
     * @return $this
     */
    public function seeJson(array $data = null, $negate = false)
    {
        if (is_null($data)) {
            $decodedResponse = json_decode($this->response->getContent(), true);

            if (is_null($decodedResponse) || $decodedResponse === false) {
                PHPUnit::fail(
                    "JSON was not returned from [{$this->currentUri}]."
                );
            }

            return $this->seeJsonContains($decodedResponse, $negate);
        }

        return $this->seeJsonContains($data, $negate);
    }

    /**
     * Assert that the response doesn't contain JSON.
     *
     * @param array|null $data
     * @return $this
     */
    public function dontSeeJson(array $data = null)
    {
        return $this->seeJson($data, true);
    }

    /**
     * Assert that the JSON response has a given structure.
     *
     * @param array|null $structure
     * @param array|null $responseData
     * @return $this
     */
    public function seeJsonStructure(array $structure = null, $responseData = null)
    {
        $this->response->assertJsonStructure($structure, $responseData);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON.
     *
     * @param array $data
     * @param bool $negate
     * @return $this
     */
    protected function seeJsonContains(array $data, $negate = false)
    {
        if ($negate) {
            $this->response->assertJsonMissing($data, false);
        } else {
            $this->response->assertJsonFragment($data);
        }

        return $this;
    }

    /**
     * Assert that the response doesn't contain the given JSON.
     *
     * @param array $data
     * @return $this
     */
    protected function seeJsonDoesntContains(array $data)
    {
        $this->response->assertJsonMissing($data, false);

        return $this;
    }

    /**
     * Format the given key and value into a JSON string for expectation checks.
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    protected function formatToExpectedJson($key, $value)
    {
        $expected = json_encode([$key => $value]);

        if (Str::startsWith($expected, '{')) {
            $expected = substr($expected, 1);
        }

        if (Str::endsWith($expected, '}')) {
            $expected = substr($expected, 0, -1);
        }

        return $expected;
    }

    public function adminAjaxCall($method, $route, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {


        $this->_last_response = "";

        if (!Str::startsWith($route, ["http", "https", "localhost"])) {
            $uri = str_replace(site_url(), "", admin_url("admin-ajax.php"));

            $query = [
                "action" => "wpwhales",
                "route"  => $route
            ];
            $url_parts = parse_url($uri);
            if (!empty($url_parts["query"])) {
                parse_str($url_parts["query"], $query);
            }


            $uri = $uri . "?" . http_build_query($query);


            $this->currentUri = $this->prepareUrlForRequest($uri);

        } else {
            $route = Str::replace(["https://", "http://", "www", "localhost"], ["", "", ""], $route);
            $uri = $route;

        }
        $this->currentUri = $uri;


        $server["PHP_SELF"] = $uri;
        $server["DOCUMENT_URI"] = $uri;
        $server["SCRIPT_NAME"] = $uri;
        $server["SCRIPT_NAME"] = $uri;
        $server["SCRIPT_FILENAME"] = ABSPATH . "." . $uri;
        $server["HTTPS"] = "https";
        $server["SERVER_PORT"] = 443;

        $symfonyRequest = SymfonyRequest::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );

        $this->app['request'] = Request::createFromBase($symfonyRequest);


        try {

            ob_start();
            $response = $this->app->handle($this->app['request']);
            $this->_last_response = ob_get_clean();

            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );


        } catch (\WPDieException $e) {


            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );
        }


    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @return \WPWhales\Testing\TestResponse
     */
    public function wordpressCall($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->_last_response = "";

        $this->currentUri = $this->prepareUrlForRequest($uri);


        $hook = "template_redirect";
        $priority = 1;
        if (isset($parameters["hook"])) {
            $hook = $parameters["hook"]["name"];
            $priority = $parameters["hook"]["priority"];

            unset($parameters["hook"]);
        }
        $symfonyRequest = SymfonyRequest::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );


        $this->app['request'] = Request::createFromBase($symfonyRequest);

        try {


            global $wp_filter;



            ob_start();
            $response = $this->app->handle($this->app['request']);

            $this->assertArrayHasKey($priority, $wp_filter[$hook],"Hook {$hook} is not binded in the code for the route {$uri}");



            $instance = Collection::make($wp_filter[$hook][$priority]);
            $instance = $instance->filter(function($filter,$key){

                return $filter["function"][0] ===$this->app && str_contains($key,"wordpressRouteHandler");
            })->first()["function"];
            if ($instance[0] !== $this->app) {
                wp_die("hook is not binded in the code");
            }

            $method = $instance[1];
            $instance[0]->{$method}();
            $this->_last_response = ob_get_clean();

            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );


        } catch (\WPDieException $e) {

            $this->_last_response = ob_get_clean();

            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );
        }


    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @return \WPWhales\Testing\TestResponse
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->_last_response = "";

        $this->currentUri = $this->prepareUrlForRequest($uri);

        $symfonyRequest = SymfonyRequest::create(
            $this->currentUri, $method, $parameters,
            $cookies, $files, $server, $content
        );


        $this->app['request'] = Request::createFromBase($symfonyRequest);

        try {


            ob_start();
            $response = $this->app->handle($this->app['request']);
            $this->_last_response = ob_get_clean();

            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );


        } catch (\WPDieException $e) {

            $this->_last_response = ob_get_clean();

            return $this->response = TestResponse::fromBaseResponse(
                $this->app->getResponse()
            );
        }


    }

    /**
     * Turn the given URI into a fully qualified URL.
     *
     * @param string $uri
     * @return string
     */
    protected function prepareUrlForRequest($uri)
    {

        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

//        if (!Str::startsWith($uri, 'http')) {
//            $uri = $this->baseUrl . '/' . $uri;
//        }

        return trim($uri, '/');
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param array $headers
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        $server = [];
        $prefix = 'HTTP_';

        foreach ($headers as $name => $value) {
            $name = strtr(strtoupper($name), '-', '_');

            if (!Str::startsWith($name, $prefix) && $name != 'CONTENT_TYPE') {
                $name = $prefix . $name;
            }

            $server[$name] = $value;
        }

        return $server;
    }

    /**
     * Assert that the client response has an OK status code.
     *
     * @return void
     */
    public function assertResponseOk()
    {
        $this->response->assertOk();
    }

    /**
     * Assert that the client response has a given status code.
     *
     * @param int $status
     * @return void
     */
    public function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }

    /**
     * Asserts that the status code of the response matches the given code.
     *
     * @param int $status
     * @return $this
     */
    protected function seeStatusCode($status)
    {
        $this->assertResponseStatus($status);

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed $value
     * @return $this
     */
    protected function seeHeader($headerName, $value = null)
    {
        $this->response->assertHeader($headerName, $value);

        return $this;
    }

    /**
     * Disable middleware for the test.
     *
     * @return $this
     */
    public function withoutMiddleware()
    {
        $this->app->instance('middleware.disable', true);

        return $this;
    }
}
