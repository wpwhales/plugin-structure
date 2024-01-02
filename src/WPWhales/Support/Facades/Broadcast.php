<?php

namespace WPWhales\Support\Facades;

use WPWhales\Contracts\Broadcasting\Factory as BroadcastingFactoryContract;

/**
 * @method static void routes(array|null $attributes = null)
 * @method static void userRoutes(array|null $attributes = null)
 * @method static void channelRoutes(array|null $attributes = null)
 * @method static string|null socket(\WPWhales\Http\Request|null $request = null)
 * @method static \WPWhales\Broadcasting\PendingBroadcast event(mixed|null $event = null)
 * @method static void queue(mixed $event)
 * @method static mixed connection(string|null $driver = null)
 * @method static mixed driver(string|null $name = null)
 * @method static \Pusher\Pusher pusher(array $config)
 * @method static \Ably\AblyRest ably(array $config)
 * @method static string getDefaultDriver()
 * @method static void setDefaultDriver(string $name)
 * @method static void purge(string|null $name = null)
 * @method static \WPWhales\Broadcasting\BroadcastManager extend(string $driver, \Closure $callback)
 * @method static \WPWhales\Contracts\Foundation\Application getApplication()
 * @method static \WPWhales\Broadcasting\BroadcastManager setApplication(\WPWhales\Contracts\Foundation\Application $app)
 * @method static \WPWhales\Broadcasting\BroadcastManager forgetDrivers()
 * @method static mixed auth(\WPWhales\Http\Request $request)
 * @method static mixed validAuthenticationResponse(\WPWhales\Http\Request $request, mixed $result)
 * @method static void broadcast(array $channels, string $event, array $payload = [])
 * @method static array|null resolveAuthenticatedUser(\WPWhales\Http\Request $request)
 * @method static void resolveAuthenticatedUserUsing(\Closure $callback)
 * @method static \WPWhales\Broadcasting\Broadcasters\Broadcaster channel(\WPWhales\Contracts\Broadcasting\HasBroadcastChannel|string $channel, callable|string $callback, array $options = [])
 * @method static \WPWhales\Support\Collection getChannels()
 *
 * @see \WPWhales\Broadcasting\BroadcastManager
 * @see \WPWhales\Broadcasting\Broadcasters\Broadcaster
 */
class Broadcast extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BroadcastingFactoryContract::class;
    }
}
