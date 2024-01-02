<?php

namespace WPWhales\Support\Facades;

/**
 * @method static \WPWhales\Pipeline\Pipeline send(mixed $passable)
 * @method static \WPWhales\Pipeline\Pipeline through(array|mixed $pipes)
 * @method static \WPWhales\Pipeline\Pipeline pipe(array|mixed $pipes)
 * @method static \WPWhales\Pipeline\Pipeline via(string $method)
 * @method static mixed then(\Closure $destination)
 * @method static mixed thenReturn()
 * @method static \WPWhales\Pipeline\Pipeline setContainer(\WPWhales\Contracts\Container\Container $container)
 * @method static \WPWhales\Pipeline\Pipeline|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \WPWhales\Pipeline\Pipeline|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 *
 * @see \WPWhales\Pipeline\Pipeline
 */
class Pipeline extends Facade
{
    /**
     * Indicates if the resolved instance should be cached.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pipeline';
    }
}
