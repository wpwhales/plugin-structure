<?php

namespace WPWCore\Cache;


use Closure;
use WPWCore\Cache\NullStore;
use WPWCore\Cache\Repository;
use WPWhales\Contracts\Cache\Factory as FactoryContract;
use WPWhales\Contracts\Cache\Store;
use InvalidArgumentException;
use WPWhales\Contracts\Events\Dispatcher;

/**
 * @mixin \WPWhales\Cache\Repository
 * @mixin \WPWhales\Contracts\Cache\LockProvider
 */
class CacheManager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \WPWhales\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new Cache manager instance.
     *
     * @param \WPWhales\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a cache store instance by name, wrapped in a repository.
     *
     * @param string|null $name
     * @return \WPWhales\Contracts\Cache\Repository
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] ??= $this->resolve($name);
    }

    /**
     * Get a cache driver instance.
     *
     * @param string|null $driver
     * @return \WPWhales\Contracts\Cache\Repository
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @param \Closure $callback
     * @return $this
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    /**
     * Call a custom driver creator.
     *
     * @param array $config
     * @return mixed
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Resolve the given store.
     *
     * @param string $name
     * @return \WPWhales\Contracts\Cache\Repository
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }

        $driverMethod = 'create' . ucfirst($config['driver']) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        }

        throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
    }


    protected function createWpDriver(array $config){
        return $this->repository(
            new WpObjectCacheStore()
        );
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param array $config
     * @return \WPWCore\Cache\Repository
     */
    protected function createFileDriver(array $config)
    {
        return $this->repository(
            (new FileStore($this->app['files'], $config['path'], $config['permission'] ?? null))
                ->setLockDirectory($config['lock_path'] ?? null)
        );
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return \WPWCore\Cache\Repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @param array $config
     * @return \WPWCore\Cache\Repository
     */
    protected function createArrayDriver(array $config)
    {
        return $this->repository(new ArrayStore($config['serialize'] ?? false));
    }


    /**
     * Create a new cache repository with the given implementation.
     *
     * @param \WPWhales\Contracts\Cache\Store $store
     * @return \WPWCore\Cache\Repository
     */
    public function repository(Store $store)
    {
        return \WPWCore\Support\tap(new Repository($store), function ($repository) {
            $this->setEventDispatcher($repository);
        });
    }

    /**
     * Set the event dispatcher on the given repository instance.
     *
     * @param \WPWCore\Cache\Repository $repository
     * @return void
     */
    protected function setEventDispatcher(Repository $repository)
    {
        if (!$this->app->bound(Dispatcher::class)) {
            return;
        }

        $repository->setEventDispatcher(
            $this->app[Dispatcher::class]
        );
    }

    /**
     * Re-set the event dispatcher on all resolved cache repositories.
     *
     * @return void
     */
    public function refreshEventDispatcher()
    {
        array_map([$this, 'setEventDispatcher'], $this->stores);
    }

    /**
     * Get the cache prefix.
     *
     * @param array $config
     * @return string
     */
    protected function getPrefix(array $config)
    {
        return $config['prefix'] ?? $this->app['config']['cache.prefix'];
    }

    /**
     * Get the cache connection configuration.
     *
     * @param string $name
     * @return array|null
     */
    protected function getConfig($name)
    {
        if (!is_null($name) && $name !== 'null') {
            return $this->app['config']["cache.stores.{$name}"];
        }

        return ['driver' => 'null'];
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return "wp";

    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['cache.default'] = $name;
    }

    /**
     * Unset the given driver instances.
     *
     * @param array|string|null $name
     * @return $this
     */
    public function forgetDriver($name = null)
    {
        $name ??= $this->getDefaultDriver();

        foreach ((array)$name as $cacheName) {
            if (isset($this->stores[$cacheName])) {
                unset($this->stores[$cacheName]);
            }
        }

        return $this;
    }

    /**
     * Disconnect the given driver and remove from local cache.
     *
     * @param string|null $name
     * @return void
     */
    public function purge($name = null)
    {
        $name ??= $this->getDefaultDriver();

        unset($this->stores[$name]);
    }


    /**
     * Set the application instance used by the manager.
     *
     * @param \WPWhales\Contracts\Foundation\Application $app
     * @return $this
     */
    public function setApplication($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}
