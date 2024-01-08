<?php

namespace WPWCore\Routing;

use Carbon\Carbon;
use WPWhales\Contracts\Routing\UrlRoutable;
use WPWhales\Support\Arr;
use WPWhales\Support\InteractsWithTime;
use WPWhales\Support\Str;
use WPWCore\Application;
use WPWhales\Support\Traits\Macroable;
use WPWCore\Http\Request;

class UrlGenerator
{

    use InteractsWithTime, Macroable;


    /**
     * The application instance.
     *
     * @var \WPWCore\Application
     */
    protected $app;

    /**
     * The forced URL root.
     *
     * @var string
     */
    protected $forcedRoot;

    /**
     * The forced schema for URLs.
     *
     * @var string
     */
    protected $forceScheme;

    /**
     * The cached URL root.
     *
     * @var string|null
     */
    protected $cachedRoot;

    /**
     * A cached copy of the URL schema for the current request.
     *
     * @var string|null
     */
    protected $cachedSchema;


    /**
     * The encryption key resolver callable.
     *
     * @var callable
     */
    protected $keyResolver;

    /**
     * Create a new URL redirector instance.
     *
     * @param \WPWCore\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->setKeyResolver(function () {
            return $this->app->make('config')->get('app.key');
        });

        $this->forceRootUrl(site_url());
    }

    /**
     * Get the full URL for the current request.
     *
     * @return string
     */
    public function full()
    {
        return $this->app->make('request')->fullUrl();
    }

    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        return $this->to($this->app->make('request')->getPathInfo());
    }

    /**
     * Generate a url for the application.
     *
     * @param string $path
     * @param array $extra
     * @param bool $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        // First we will check if the URL is already a valid URL. If it is we will not
        // try to generate a new one but will simply return the URL as is, which is
        // convenient since developers do not always have to check if it's valid.
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $scheme = $this->getSchemeForUrl($secure);

        $extra = $this->formatParameters($extra);

        $tail = implode('/', array_map(
                'rawurlencode', (array)$extra)
        );

        // Once we have the scheme we will compile the "tail" by collapsing the values
        // into a single string delimited by slashes. This just makes it convenient
        // for passing the array of parameters to this URL as a list of segments.
        $root = $this->getRootUrl($scheme);

        return $this->trimUrl($root, $path, $tail);
    }

    /**
     * Generate a secure, absolute URL to the given path.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * Generate a URL to an application asset.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->formatScheme($secure));

        return $this->removeIndex($root) . '/' . trim($path, '/');
    }

    /**
     * Generate a URL to an application asset from a root domain such as CDN etc.
     *
     * @param string $root
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function assetFrom($root, $path, $secure = null)
    {
        // Once we get the root URL, we will check to see if it contains an index.php
        // file in the paths. If it does, we will remove it since it is not needed
        // for asset paths, but only for routes to endpoints in the application.
        $root = $this->getRootUrl($this->formatScheme($secure), $root);

        return $this->removeIndex($root) . '/' . trim($path, '/');
    }

    /**
     * Remove the index.php file from a path.
     *
     * @param string $root
     * @return string
     */
    protected function removeIndex($root)
    {
        $i = 'index.php';

        return Str::contains($root, $i) ? str_replace('/' . $i, '', $root) : $root;
    }

    /**
     * Generate a URL to a secure asset.
     *
     * @param string $path
     * @return string
     */
    public function secureAsset($path)
    {
        return $this->asset($path, true);
    }

    /**
     * Force the schema for URLs.
     *
     * @param string $schema
     * @return void
     */
    public function forceScheme($schema)
    {
        $this->cachedSchema = null;

        $this->forceScheme = $schema . '://';
    }

    /**
     * Get the default scheme for a raw URL.
     *
     * @param bool|null $secure
     * @return string
     */
    public function formatScheme($secure)
    {
        if (!is_null($secure)) {
            return $secure ? 'https://' : 'http://';
        }

        if (is_null($this->cachedSchema)) {
            $this->cachedSchema = $this->forceScheme ?: $this->app->make('request')->getScheme() . '://';
        }

        return $this->cachedSchema;
    }

    /**
     * Get the URL to a named route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param bool|null $secure
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $secure = null)
    {
        if (!isset($this->app->router->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        $uri = $this->app->router->namedRoutes[$name];

        $parameters = $this->formatParameters($parameters);

        $uri = preg_replace_callback('/\[([^\]]*)\]$/', function ($matches) use ($uri, &$parameters) {
            $uri = $this->replaceRouteParameters($matches[1], $parameters);

            return ($matches[1] == $uri) ? '' : $uri;
        }, $uri);

        $uri = $this->replaceRouteParameters($uri, $parameters);

        $uri = $this->to($uri, [], $secure);

        if (!empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return $uri;
    }


    /**
     * Get the URL to a named admin ajax route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param bool|null $secure
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function adminAjaxRoute($name, $parameters = [], $secure = null)
    {
        if (!isset($this->app->adminAjaxRouter->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        $uri = $this->app->adminAjaxRouter->namedRoutes[$name];
        $schema = $secure ? "https" : "http";
        $parameters["action"] = "wpwhales";
        $parameters["route"] = $uri;
        $uri = admin_url("admin-ajax.php", $schema);


        $parameters = $this->formatParameters($parameters);

        $uri = preg_replace_callback('/\[([^\]]*)\]$/', function ($matches) use ($uri, &$parameters) {
            $uri = $this->replaceRouteParameters($matches[1], $parameters);

            return ($matches[1] == $uri) ? '' : $uri;
        }, $uri);

        $uri = $this->replaceRouteParameters($uri, $parameters);

        $uri = $this->to($uri, [], $secure);

        if (!empty($parameters)) {
            $uri .= '?' . http_build_query($parameters);
        }

        return $uri;
    }

    /**
     * Determine if the given path is a valid URL.
     *
     * @param string $path
     * @return bool
     */
    public function isValidUrl($path)
    {
        if (Str::startsWith($path, ['#', '//', 'mailto:', 'tel:', 'sms:', 'http://', 'https://'])) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get the scheme for a raw URL.
     *
     * @param bool|null $secure
     * @return string
     */
    protected function getSchemeForUrl($secure)
    {
        if (is_null($secure)) {
            if (is_null($this->cachedSchema)) {
                $this->cachedSchema = $this->formatScheme($secure);
            }

            return $this->cachedSchema;
        }

        return $secure ? 'https://' : 'http://';
    }

    /**
     * Format the array of URL parameters.
     *
     * @param mixed|array $parameters
     * @return array
     */
    public function formatParameters($parameters)
    {
        $parameters = Arr::wrap($parameters);

        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $parameter->getRouteKey();
            }
        }

        return $parameters;
    }

    /**
     * Replace the route parameters with their parameter.
     *
     * @param string $route
     * @param array $parameters
     * @return string
     */
    protected function replaceRouteParameters($route, &$parameters = [])
    {
        return preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
        }, $route);
    }

    /**
     * Get the base URL for the request.
     *
     * @param string $scheme
     * @param string $root
     * @return string
     */
    protected function getRootUrl($scheme, $root = null)
    {
        if (is_null($root)) {
            if (is_null($this->cachedRoot)) {
                $this->cachedRoot = $this->forcedRoot ?: $this->app->make('request')->root();
            }

            $root = $this->cachedRoot;
        }


        $start = Str::startsWith($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~' . $start . '~', $scheme, $root, 1);
    }

    /**
     * Set the forced root URL.
     *
     * @param string $root
     * @return void
     */
    public function forceRootUrl($root)
    {
        $this->forcedRoot = rtrim($root, '/');

        $this->cachedRoot = null;
    }

    /**
     * Format the given URL segments into a single URL.
     *
     * @param string $root
     * @param string $path
     * @param string $tail
     * @return string
     */
    protected function trimUrl($root, $path, $tail = '')
    {
        return trim($root . '/' . trim($path . '/' . $tail, '/'), '/');
    }


    /**
     * Create a signed route URL for a named route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param \DateTimeInterface|\DateInterval|int|null $expiration
     * @param bool $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true)
    {
        $parameters = Arr::wrap($parameters);

        if (array_key_exists('signature', $parameters)) {
            throw new InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your route parameter.'
            );
        }

        if ($expiration) {
            $parameters = $parameters + ['expires' => $this->availableAt($expiration)];
        }

        ksort($parameters);

        $key = call_user_func($this->keyResolver);

        return $this->route($name, $parameters + [
                'signature' => hash_hmac('sha256', $this->route($name, $parameters, $absolute), $key),
            ], $absolute);
    }


    /**
     * Create a signed route URL for a named admin ajax route.
     *
     * @param string $name
     * @param mixed $parameters
     * @param \DateTimeInterface|\DateInterval|int|null $expiration
     * @param bool $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function signedAdminAjaxRoute($name, $parameters = [], $expiration = null, $absolute = true)
    {
        $parameters = Arr::wrap($parameters);

        if (array_key_exists('signature', $parameters)) {
            throw new InvalidArgumentException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your route parameter.'
            );
        }

        if ($expiration) {
            $parameters = $parameters + ['expires' => $this->availableAt($expiration)];
        }

        ksort($parameters);

        $key = call_user_func($this->keyResolver);

        return $this->adminAjaxRoute($name, $parameters + [
                'signature' => hash_hmac('sha256', $this->adminAjaxRoute($name, $parameters, $absolute), $key),
            ], $absolute);
    }


    /**
     * Create a temporary signed route URL for a named route.
     *
     * @param string $name
     * @param \DateTimeInterface|\DateInterval|int $expiration
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    public function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true)
    {
        return $this->signedRoute($name, $parameters, $expiration, $absolute);
    }

    /**
     * Determine if the given request has a valid signature.
     *
     * @param \WPWCore\Http\Request $request
     * @param bool $absolute
     * @return bool
     */
    public function hasValidSignature(Request $request, $absolute = true)
    {
        return $this->hasCorrectSignature($request, $absolute)
            && $this->signatureHasNotExpired($request);
    }

    /**
     * Determine if the given request has a valid signature for a relative URL.
     *
     * @param \WPWCore\Http\Request $request
     * @return bool
     */
    public function hasValidRelativeSignature(Request $request)
    {
        return $this->hasValidSignature($request, false);
    }

    /**
     * Determine if the signature from the given request matches the URL.
     *
     * @param \WPWCore\Http\Request $request
     * @param bool $absolute
     * @return bool
     */
    public function hasCorrectSignature(Request $request, $absolute = true)
    {
        $url = $absolute ? $request->url() : '/' . $request->path();

        $original = rtrim($url . '?' . Arr::query(
                Arr::except($request->query(), 'signature')
            ), '?');

        $signature = hash_hmac('sha256', $original, call_user_func($this->keyResolver));

        return hash_equals($signature, (string)$request->query('signature', ''));
    }

    /**
     * Determine if the expires timestamp from the given request is not from the past.
     *
     * @param \WPWCore\Http\Request $request
     * @return bool
     */
    public function signatureHasNotExpired(Request $request)
    {
        $expires = $request->query('expires');

        return !($expires && Carbon::now()->getTimestamp() > $expires);
    }

    /**
     * Set the encryption key resolver.
     *
     * @param callable $keyResolver
     * @return $this
     */
    public function setKeyResolver(callable $keyResolver)
    {
        $this->keyResolver = $keyResolver;

        return $this;
    }

    /**
     * Get the URL for the previous request.
     *
     * @param mixed $fallback
     * @return string
     */
    public function previous($fallback = false)
    {


        $request = $this->app->make('request');

        $referrer = $request->headers->get('referer');


        //need to implement $this->getPreviousUrlFromSession();
        $url = $referrer ? $this->to($referrer) : "/";

        if ($url) {
            return $url;
        } elseif ($fallback) {
            return $this->to($fallback);
        }

        return $this->to('/');
    }
}
