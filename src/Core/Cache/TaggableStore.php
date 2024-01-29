<?php

namespace WPWCore\Cache;

use WPWCore\Cache\TaggedCache;
use WPWCore\Cache\TagSet;
use WPWhales\Contracts\Cache\Store;

abstract class TaggableStore implements Store
{
    /**
     * Begin executing a new tags operation.
     *
     * @param  array|mixed  $names
     * @return \WPWhales\Cache\TaggedCache
     */
    public function tags($names)
    {

        if((is_array($names) && count($names)>1) || count(func_get_args())>1 ){
            //allow only single tags for now.
            throw new \ArgumentCountError("Please provide only 1 tag");
        }
        $tagSet = new TagSet(clone $this, is_array($names) ? $names : func_get_args());
        $tagSet->save();

        return new TaggedCache(clone $this,$tagSet );
    }
}
