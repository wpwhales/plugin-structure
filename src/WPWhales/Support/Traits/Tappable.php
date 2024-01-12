<?php

namespace WPWhales\Support\Traits;

trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param  callable|null  $callback
     * @return $this|\WPWhales\Support\HigherOrderTapProxy
     */
    public function tap($callback = null)
    {
        return \WPWCore\Support\tap($this, $callback);
    }
}
