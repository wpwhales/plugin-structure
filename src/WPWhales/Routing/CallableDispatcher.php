<?php

namespace WPWhales\Routing;

use WPWhales\Container\Container;
use WPWhales\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use ReflectionFunction;

class CallableDispatcher implements CallableDispatcherContract
{
    use ResolvesRouteDependencies;

    /**
     * The container instance.
     *
     * @var \WPWhales\Container\Container
     */
    protected $container;

    /**
     * Create a new callable dispatcher instance.
     *
     * @param  \WPWhales\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch a request to a given callable.
     *
     * @param  \WPWhales\Routing\Route  $route
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(Route $route, $callable)
    {
        return $callable(...array_values($this->resolveParameters($route, $callable)));
    }

    /**
     * Resolve the parameters for the callable.
     *
     * @param  \WPWhales\Routing\Route  $route
     * @param  callable  $callable
     * @return array
     */
    protected function resolveParameters(Route $route, $callable)
    {
        return $this->resolveMethodDependencies($route->parametersWithoutNulls(), new ReflectionFunction($callable));
    }
}
