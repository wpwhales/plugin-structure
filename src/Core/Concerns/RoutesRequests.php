<?php

namespace WPWCore\Concerns;

use Closure;
use FastRoute\Dispatcher;
use WPWCore\Routing\Middleware\VerifyCsrfToken;
use WPWCore\View\View;
use WPWhales\Contracts\Support\Responsable;
use WPWhales\Http\Exceptions\HttpResponseException;
use WPWCore\Http\Request;
use WPWhales\Http\Response;
use WPWhales\Support\Arr;
use WPWhales\Support\Str;
use WPWCore\Http\Request as LumenRequest;
use WPWCore\Routing\Closure as RoutingClosure;
use WPWCore\Routing\Controller as LumenController;
use WPWCore\Routing\Pipeline;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait RoutesRequests
{
    /**
     * All of the global middleware for the application.
     *
     * @var array
     */
    protected $middleware = [
        VerifyCsrfToken::class
    ];

    /**
     * All of the route specific middleware short-hands.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * The current route being dispatched.
     *
     * @var array
     */
    protected $currentRoute;

    /**
     * The FastRoute dispatcher.
     *
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;


    /**
     * The FastRoute dispatcher.
     *
     * @var Response
     */
    protected $response;

    /**
     * Add new middleware to the application.
     *
     * @param \Closure|array $middleware
     * @return $this
     */
    public function middleware($middleware)
    {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Define the route middleware for the application.
     *
     * @param array $middleware
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * Dispatch request and save response.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return void
     */
    public function handle(SymfonyRequest $request)
    {
        $response = $this->dispatch($request);


        //It means no route found so we'll terminate the app and
        // let wordpress handle the rest of the stuff

        $this->handleResponse($response);

    }


    private function handleResponse($response, $callback = null)
    {



        if ($response !== false && $this->shouldSendResponse()) {

            $this->response = $this->attachQueuedCookiesWithResponse($response);

            if ($response instanceof SymfonyResponse) {

                $response->send();
            } else {
                echo (string)$response;
            }


            if (count($this->middleware) > 0) {
                $this->callTerminableMiddleware($response);
            }

            if (is_callable($callback)) {
                $callback();
            }
            wp_die();
        }
    }

    private function attachQueuedCookiesWithResponse($response)
    {


        return $this->make("cookie")->attachCookiesInResponse($response);
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Run the application and send the response.
     *
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @return void
     */
    public function run($request = null)
    {
        $response = $this->dispatch($request);


        //It means no route found so we'll terminate the app and
        // let wordpress handle the rest of the stuff
        $this->handleResponse($response, function () {
            $this->app->terminate();
        });

        //TODO Remove all instances for the app container except the
        // cookies , session , validator


    }

    /**
     * Call the terminable middleware.
     *
     * @param mixed $response
     * @return void
     */
    protected function callTerminableMiddleware($response)
    {
        if ($this->shouldSkipMiddleware()) {
            return;
        }

        $response = $this->prepareResponse($response);

        foreach ($this->middleware as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            $instance = $this->make(explode(':', $middleware)[0]);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($this->make('request'), $response);
            }
        }
    }


    /**
     * Dispatch the incoming request.
     *
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @return \WPWhales\Http\Response|false
     */
    public function dispatch($request = null)
    {


        [$method, $pathInfo] = $this->parseIncomingRequest($request);


        //Start the session as well so that the components can utilize it
        $this->initSession();

        try {

            //First check if route exists
            if (isset($this->router->getRoutes()[$method . $pathInfo])) {
                $this->currentRoute = [
                    true, $this->router->getRoutes()[$method . $pathInfo]['action'], []
                ];
                //Then boot the app first
                $this->boot();

                return $this->sendThroughPipeline($this->middleware, function ($request) use ($method, $pathInfo) {
                    $this->instance(\WPWhales\Http\Request::class, $request);


                    return $this->handleFoundRoute([
                        true, $this->router->getRoutes()[$method . $pathInfo]['action'], []
                    ]);

                });
            }
            return false;


        } catch (Throwable $e) {


            return $this->prepareResponse($this->sendExceptionToHandler($e));
        }
    }

    /**
     * Parse the incoming request and return the method and path info.
     *
     * @param \Symfony\Component\HttpFoundation\Request|null $request
     * @return array
     */
    protected function parseIncomingRequest($request)
    {

        if (!$request) {
            $request = LumenRequest::capture();
        }

        $paths = [$request->getMethod()];

        $this->instance(\WPWhales\Http\Request::class, $this->prepareRequest($request));
        $this->instance(Request::class, $this->prepareRequest($request));

        if (wp_doing_ajax()) {
            $action = $request->get("action");
            $route = $request->get("route");

            if ($action === "wpwhales" && !empty($route)) {
                $paths[] = '/' . trim($route, '/');

                return $paths;
            }

        }

        $paths[] = '/' . trim($request->getPathInfo(), '/');

        return $paths;
    }


    public function shouldSendResponse()
    {

        return !empty($this->currentRoute);
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return \FastRoute\Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function ($r) {
            foreach ($this->router->getRoutes() as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    /**
     * Set the FastRoute dispatcher instance.
     *
     * @param \FastRoute\Dispatcher $dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param array $routeInfo
     * @return mixed
     */
    protected function handleDispatcherResponse($routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($routeInfo[1]);
            case Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo);
        }
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param array $routeInfo
     * @return mixed
     */
    protected function handleFoundRoute($routeInfo)
    {


        $this['request']->setRouteResolver(function () {
            return $this->currentRoute;
        });

        $action = $routeInfo[1];

        // Pipe through route middleware...
        if (isset($action['middleware'])) {
            $middleware = $this->gatherMiddlewareClassNames($action['middleware']);

            return $this->prepareResponse($this->sendThroughPipeline($middleware, function () {
                return $this->callActionOnArrayBasedRoute($this['request']->route());
            }));
        }

        return $this->prepareResponse(
            $this->callActionOnArrayBasedRoute($routeInfo)
        );
    }

    /**
     * Call the Closure or invokable on the array based route.
     *
     * @param array $routeInfo
     * @return mixed
     */
    protected function callActionOnArrayBasedRoute($routeInfo)
    {
        $action = $routeInfo[1];

        if (isset($action['uses'])) {
            return $this->prepareResponse($this->callControllerAction($routeInfo));
        }

        foreach ($action as $value) {
            if ($value instanceof Closure) {
                $callable = $value->bindTo(new RoutingClosure);
                break;
            }

            if (is_object($value) && is_callable($value)) {
                $callable = $value;
                break;
            }
        }

        if (!isset($callable)) {
            throw new RuntimeException('Unable to resolve route handler.');
        }

        try {
            return $this->prepareResponse($this->call($callable, $routeInfo[2]));
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Call a controller based route.
     *
     * @param array $routeInfo
     * @return mixed
     */
    protected function callControllerAction($routeInfo)
    {
        $uses = $routeInfo[1]['uses'];

        if (is_string($uses) && !Str::contains($uses, '@')) {
            $uses .= '@__invoke';
        }

        [$controller, $method] = explode('@', $uses);

        if (!method_exists($instance = $this->make($controller), $method)) {
            throw new NotFoundHttpException;
        }

        if ($instance instanceof LumenController) {
            return $this->callLumenController($instance, $method, $routeInfo);
        } else {
            return $this->callControllerCallable(
                [$instance, $method], $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a Lumen controller.
     *
     * @param mixed $instance
     * @param string $method
     * @param array $routeInfo
     * @return mixed
     */
    protected function callLumenController($instance, $method, $routeInfo)
    {
        $middleware = $instance->getMiddlewareForMethod($method);

        if (count($middleware) > 0) {
            return $this->callLumenControllerWithMiddleware(
                $instance, $method, $routeInfo, $middleware
            );
        } else {
            return $this->callControllerCallable(
                [$instance, $method], $routeInfo[2]
            );
        }
    }

    /**
     * Send the request through a set of controller middleware.
     *
     * @param mixed $instance
     * @param string $method
     * @param array $routeInfo
     * @param array $middleware
     * @return mixed
     */
    protected function callLumenControllerWithMiddleware($instance, $method, $routeInfo, $middleware)
    {
        $middleware = $this->gatherMiddlewareClassNames($middleware);

        return $this->sendThroughPipeline($middleware, function () use ($instance, $method, $routeInfo) {
            return $this->callControllerCallable([$instance, $method], $routeInfo[2]);
        });
    }

    /**
     * Call a controller callable and return the response.
     *
     * @param callable $callable
     * @param array $parameters
     * @return \WPWhales\Http\Response
     */
    protected function callControllerCallable(callable $callable, array $parameters = [])
    {
        try {
            return $this->prepareResponse(
                $this->call($callable, $parameters)
            );
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param string|array $middleware
     * @return array
     */
    protected function gatherMiddlewareClassNames($middleware)
    {
        $middleware = is_string($middleware) ? explode('|', $middleware) : (array)$middleware;

        return array_map(function ($name) {
            [$name, $parameters] = array_pad(explode(':', $name, 2), 2, null);

            return Arr::get($this->routeMiddleware, $name, $name) . ($parameters ? ':' . $parameters : '');
        }, $middleware);
    }

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param array $middleware
     * @param \Closure $then
     * @return mixed
     */
    protected function sendThroughPipeline(array $middleware, Closure $then)
    {



        if (count($middleware) > 0 && !$this->shouldSkipMiddleware()) {
            return (new Pipeline($this))
                ->send($this->make('request'))
                ->through($middleware)
                ->then($then);
        }

        return $then($this->make('request'));
    }

    /**
     * Prepare the response for sending.
     *
     * @param mixed $response
     * @return \WPWhales\Http\Response
     */
    public function prepareResponse($response)
    {
        $request = \WPWCore\app(Request::class);


        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        if ($response instanceof PsrResponseInterface) {
            $response = (new HttpFoundationFactory)->createResponse($response);
        } elseif ($response instanceof View) {
            //if it's a ajax call then transform it into json
            if (wp_doing_ajax()) {
                $ajax_response = new Response(["html" => $response->render()]);
                $ajax_response->original = $response;

                $response = $ajax_response;
            } else {
                $response = new Response($response);
            }
        } elseif (!$response instanceof SymfonyResponse) {
            $response = new Response($response);
        } elseif ($response instanceof BinaryFileResponse) {
            $response = $response->prepare(Request::capture());
        }

        return $response->prepare($request);
    }

    /**
     * Determines whether middleware should be skipped during request.
     *
     * @return bool
     */
    protected function shouldSkipMiddleware()
    {
        return $this->bound('middleware.disable') && $this->make('middleware.disable') === true;
    }
}
