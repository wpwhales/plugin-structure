<?php

namespace WPWCore\Cache;

use Exception;

use WPWhales\Contracts\Cache\Store;

use WPWhales\Support\InteractsWithTime;

class WpObjectCacheStore extends TaggableStore implements Store
{
    use InteractsWithTime, RetrievesMultipleKeys;


    protected $group = "wpwcore";

    protected $coreGroup = "wpwcore";

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string|array $key
     * @return mixed
     */
    public function get($key)
    {

        if (is_array($key)) {
            return $this->many($key);
        }

        $value = wp_cache_get($key, $this->group);
        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @param string $group
     * @return bool
     */
    public function put($key, $value, $seconds)
    {


        return wp_cache_set($key, $value, $this->group, $seconds);

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
        return wp_cache_add($key, $value, $this->group, $seconds);

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
        return wp_cache_incr($key, $value, $this->group);

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
        return wp_cache_decr($key, $value, $this->group);
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

        return wp_cache_delete($key, $this->group);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {

        $tags = wp_cache_get("tags", $this->coreGroup);

        if ($this->coreGroup !== $this->group) {

            $flushed = wp_cache_flush_group($this->group);;

            if ($flushed) {
                $key = array_search($this->group, $tags);
                unset($tags[$key]);
                wp_cache_set("tags", array_values($tags), $this->coreGroup,0);
            }


            return $flushed;
        }


        if (is_array($tags)) {
            foreach ($tags as $tag) {
                wp_cache_flush_group($tag);
            }
        }


        return wp_cache_flush_group("wpwcore");
    }


    public function setGroup($group)
    {
        $this->group = $group;
    }


    public function getCoreGroup()
    {

        return $this->coreGroup;
    }

    public function getGroup()
    {

        return $this->group;
    }

    public function getPrefix()
    {

        return "wpwcore_";
    }


}
