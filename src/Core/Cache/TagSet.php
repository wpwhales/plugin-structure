<?php

namespace WPWCore\Cache;

use WPWhales\Contracts\Cache\Store;

class TagSet
{
    /**
     * The cache store implementation.
     *
     * @var \WPWhales\Contracts\Cache\Store
     */
    protected $store;

    /**
     * The tag names.
     *
     * @var array
     */
    protected $names = [];

    /**
     * Create a new TagSet instance.
     *
     * @param \WPWhales\Contracts\Cache\Store $store
     * @param array $names
     * @return void
     */
    public function __construct(Store $store, array $names = [])
    {
        $this->store = $store;
        $this->names = $names;


    }

    public function save()
    {

        $store = new WpObjectCacheStore();
        $tags = $store->get("tags");
        $new_tags = $this->tagIds();
        if (is_array($tags)) {
            $store->forever("tags", array_unique(array_merge($tags, $new_tags)));

        } else {
            $store->forever("tags", $new_tags);
        }


    }


    /**
     * Get an array of tag identifiers for all of the tags in the set.
     *
     * @return array
     */
    public function tagIds()
    {
        return array_map([$this, 'tagId'], $this->names);
    }

    /**
     * Get the unique tag identifier for a given tag.
     *
     * @param string $name
     * @return string
     */
    public function tagId($name)
    {
        return $this->tagKey($name);
    }

    /**
     * Get the tag identifier key for a given tag.
     *
     * @param string $name
     * @return string
     */
    public function tagKey($name)
    {
        return 'wpwcore_' . $name;
    }

    /**
     * Get all of the tag names in the set.
     *
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }
}
