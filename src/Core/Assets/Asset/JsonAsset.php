<?php

namespace WPWCore\Assets\Asset;

use WPWhales\Contracts\Support\Arrayable;
use WPWhales\Contracts\Support\Jsonable;

class JsonAsset extends TextAsset implements Jsonable, Arrayable
{
    /**
     * {@inheritdoc}
     */
    public function toJson($options = \JSON_UNESCAPED_SLASHES)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return (array) $this->decode(JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Decode JSON data.
     *
     * @param int $options
     * @param int $depth
     * @return array|null
     */
    public function decode($options = 0, $depth = 512)
    {
        return json_decode($this->contents(), null, $depth, $options);
    }
}
