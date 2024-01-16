<?php

namespace WPWCore;


use Carbon\Carbon;
use WPWCore\DashboardNotices\Notices;
use WPWCore\Http\Redirector;
use WPWCore\Routing\UrlGenerator;
use WPWhales\Container\Container;
use WPWhales\Contracts\Debug\ExceptionHandler;
use WPWhales\Support\HigherOrderTapProxy;
use WPWhales\Support\HtmlString;
/**
 * Get the available container instance.
 *
 * @param string|null $make
 * @param array $parameters
 * @return mixed|\WPWhales\Application
 */
function app($make = null, array $parameters = [])
{


    if (is_null($make)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($make, $parameters);
}
function resource_path($path){
    return app()->resourcePath($path);
}


function database_path($path = ''){

    return app()->databasePath($path);
}


/**
 * Catch a potential exception and return a default value.
 *
 * @template TRescueValue
 * @template TRescueFallback
 *
 * @param  callable(): TRescueValue  $callback
 * @param  (callable(\Throwable): TRescueFallback)|TRescueFallback  $rescue
 * @param  bool|callable  $report
 * @return TRescueValue|TRescueFallback
 */
function rescue(callable $callback, $rescue = null, $report = true)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        if (\WPWCore\Collections\value($report, $e)) {
            report($e);
        }

        return \WPWCore\Collections\value($rescue, $e);
    }
}

function app_path($path = '')
{
    return app()->path($path);
}

function base_path($path = '')
{
    return app()->basePath().($path ? '/'.$path : $path);
}

/**
 * Report an exception.
 *
 * @param  \Throwable|string  $exception
 * @return void
 */
function report($exception)
{
    if (is_string($exception)) {
        $exception = new Exception($exception);
    }

    app(ExceptionHandler::class)->report($exception);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param array|string|null $key
 * @param mixed $default
 * @return mixed
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return app('config');
    }

    if (is_array($key)) {
        return app('config')->set($key);
    }

    return app('config')->get($key, $default);
}


/**
 * Get an instance of the redirector.
 *
 * @param string|null $to
 * @param int $status
 * @param array $headers
 * @param bool|null $secure
 * @return \WPWCore\Http\Redirector|\WPWhales\Http\RedirectResponse
 */
function redirect($to = null, $status = 302, $headers = [], $secure = null)
{
    $redirector = new Redirector(app());

    if (is_null($to)) {
        return $redirector;
    }

    return $redirector->to($to, $status, $headers, $secure);
}



/**
 * Get the evaluated view contents for the given view.
 *
 * @param string $view
 * @param array $data
 * @param array $mergeData
 * @return \WPWhales\View\View
 */
function view($view = null, $data = [], $mergeData = [])
{
    $factory = app('view');

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($view, $data, $mergeData);
}


/**
 * Generate a CSRF token form field.
 *
 * @return \WPWhales\Support\HtmlString
 */
function csrf_field()
{
    return new HtmlString('<input type="hidden" name="_token" value="' . csrf_token() . '">');
}



/**
 * Get the CSRF token value.
 *
 * @return string
 *
 * @throws RuntimeException
 */
function csrf_token()
{
    $session = app('request')->session();
    if (isset($session)) {
        return $session->token();
    }
    throw new RuntimeException("Application session store not set.");

}



/**
 * Get / set the specified session value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param array|string $key
 * @param mixed $default
 * @return mixed
 */
function session($key = null, $default = null)
{


    if (is_null($key)) {
        return app("session");
    }
    if (is_array($key)) {
        return app("session")->put($key);
    }
    return app("session")->get($key, $default);
}


/**
 * Return a new response from the application.
 *
 * @param string $content
 * @param int $status
 * @param array $headers
 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
 */
function response($content = '', $status = 200, array $headers = [])
{
    $factory = new \WPWCore\Http\ResponseFactory;

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($content, $status, $headers);
}


/**
 * Retrieve an old input item.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function old($key = null, $default = null)
{
    return app('request')->old($key, $default);
}

/**
 * Call the given Closure with the given value then return the value.
 *
 * @param  mixed  $value
 * @param  callable|null  $callback
 * @return mixed
 */
function tap($value, $callback = null)
{
    if (is_null($callback)) {
        return new HigherOrderTapProxy($value);
    }

    $callback($value);

    return $value;
}


/**
 * Generate a URL to a named route.
 *
 * @param string $name
 * @param array $parameters
 * @param bool|null $secure
 * @return string
 */
function route($name, $parameters = [], $secure = null)
{
    return app('url')->route($name, $parameters, $secure);
}


/**
 * Generate a url for the application.
 *
 * @param  string|null  $path
 * @param  mixed  $parameters
 * @param  bool|null  $secure
 * @return \WPWhales\Contracts\Routing\UrlGenerator|string
 */
function url($path = null, $parameters = [], $secure = null)
{
    if (is_null($path)) {
        return app(UrlGenerator::class);
    }

    return app(UrlGenerator::class)->to($path, $parameters, $secure);
}

/**
 * Generate dashboard notice.
 *
 */
function dashboard_notice(...$parameters)
{
    return app(Notices::class)->addNotice(...array_values($parameters));
}


function now(){

    return Carbon::now();
}



/**
 * Dispatch an event and call the listeners.
 *
 * @param object|string $event
 * @param mixed $payload
 * @param bool $halt
 * @return array|null
 */
function event($event, $payload = [], $halt = false)
{
    return app('events')->dispatch($event, $payload, $halt);
}