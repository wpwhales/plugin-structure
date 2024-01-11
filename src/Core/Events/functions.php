<?php

namespace WPWhales\Events;

use Closure;
use WPWCore\Events\QueuedClosure;

if (! function_exists('WPWhales\Events\queueable')) {
    /**
     * Create a new queued Closure event listener.
     *
     * @param  \Closure  $closure
     * @return \WPWCore\Events\QueuedClosure
     */
    function queueable(Closure $closure)
    {
        return new QueuedClosure($closure);
    }
}
