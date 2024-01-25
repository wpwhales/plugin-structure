<?php

namespace WPWCore\Cache;

use Exception;

use WPWhales\Contracts\Cache\Store;

use WPWhales\Support\InteractsWithTime;

class WpObjectCacheStore implements Store
{
    use InteractsWithTime, RetrievesMultipleKeys;


    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     * @return mixed
     */
    public function get($key)
    {

        return wp_cache_get($key,"wpwcore");
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public function put($key, $value, $seconds)
    {


        return wp_cache_set($key, $value, "wpwcoress", $seconds);

    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return bool
     */
    public function add($key, $value, $seconds)
    {
        return wp_cache_add($key, $value, "wpwcore", $seconds);

    }


    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return wp_cache_increment($key,$value,"wpwcore");

    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return wp_cache_decrement($key,$value,"wpwcore");
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }


    /**
     * Remove an item from the cache.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {

        return wp_cache_delete($key,"wpwcore");
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {

        return wp_cache_flush_group("wpwcore");
    }


    public function getPrefix()
    {
        global $wpdb;
        return $wpdb->prefix;
    }




}
