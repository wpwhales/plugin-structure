<?php

namespace WPWCore;


use WPWhales\Container\Container;
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
    $redirector = new \WPWhales\Routing\Redirector(app());

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