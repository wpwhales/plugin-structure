<?php

namespace WPWCore\Cache;

use WPWCore\Cache\Events\KeyWritten;
use WPWCore\Cache\Repository;
use WPWCore\Cache\RetrievesMultipleKeys;
use WPWCore\Cache\TagSet;
use WPWhales\Contracts\Cache\Store;

class TaggedCache extends Repository
{


    /**
     * The tag set instance.
     *
     * @var \WPWCore\Cache\TagSet
     */
    protected $tags;

    /**
     * Create a new tagged cache instance.
     *
     * @param \WPWhales\Contracts\Cache\Store $store
     * @param \WPWCore\Cache\TagSet $tags
     * @return void
     */
    public function __construct(Store $store, TagSet $tags)
    {
        $names = $tags->tagIds();
        $store->setGroup(end($names));
        parent::__construct($store);

        $this->tags = $tags;

    }


    /**
     * Get the tag set instance.
     *
     * @return \WPWCore\Cache\TagSet
     */
    public function getTags()
    {
        return $this->tags;
    }
}
