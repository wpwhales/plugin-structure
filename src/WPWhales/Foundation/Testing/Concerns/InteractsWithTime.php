<?php

namespace WPWhales\Foundation\Testing\Concerns;

use WPWhales\Foundation\Testing\Wormhole;
use WPWhales\Support\Carbon;

trait InteractsWithTime
{
    /**
     * Freeze time.
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function freezeTime($callback = null)
    {
        return $this->travelTo(Carbon::now(), $callback);
    }

    /**
     * Freeze time at the beginning of the current second.
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function freezeSecond($callback = null)
    {
        return $this->travelTo(Carbon::now()->startOfSecond(), $callback);
    }

    /**
     * Begin travelling to another time.
     *
     * @param  int  $value
     * @return \WPWhales\Foundation\Testing\Wormhole
     */
    public function travel($value)
    {
        return new Wormhole($value);
    }

    /**
     * Travel to another time.
     *
     * @param  \DateTimeInterface|\Closure|\WPWhales\Support\Carbon|string|bool|null  $date
     * @param  callable|null  $callback
     * @return mixed
     */
    public function travelTo($date, $callback = null)
    {
        Carbon::setTestNow($date);

        if ($callback) {
            return tap($callback($date), function () {
                Carbon::setTestNow();
            });
        }
    }

    /**
     * Travel back to the current time.
     *
     * @return \DateTimeInterface
     */
    public function travelBack()
    {
        return Wormhole::back();
    }
}
