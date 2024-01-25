<?php

namespace WPWCore\Cache;

use Exception;
use WPWhales\Contracts\Cache\LockProvider;
use WPWhales\Contracts\Cache\Store;

use WPWhales\Support\InteractsWithTime;

class WpObjectCacheStore implements Store, LockProvider
{
    use InteractsWithTime, RetrievesMultipleKeys;



    /**
     * Create a new file cache store instance.
     *
     * @param  \WPWhales\Filesystem\Filesystem  $files
     * @param  string  $directory
     * @param  int|null  $filePermission
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {

    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * @return bool
     */
    public function put($key, $value, $seconds)
    {


    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * @return bool
     */
    public function add($key, $value, $seconds)
    {

    }


    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int
     */
    public function increment($key, $value = 1)
    {

    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {

    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }



    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {

    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {

    }



    public function getPrefix()
    {
        // TODO: Implement getPrefix() method.
    }
    public function lock($name, $seconds = 0, $owner = null)
    {
        // TODO: Implement lock() method.
    }
    public function restoreLock($name, $owner)
    {
        // TODO: Implement restoreLock() method.
    }


}
