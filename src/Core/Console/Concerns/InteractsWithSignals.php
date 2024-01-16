<?php

namespace WPWCore\Console\Concerns;

use WPWCore\Console\Signals;
use WPWhales\Support\Arr;

trait InteractsWithSignals
{
    /**
     * The signal registrar instance.
     *
     * @var \WPWCore\Console\Signals|null
     */
    protected $signals;

    /**
     * Define a callback to be run when the given signal(s) occurs.
     *
     * @param  iterable<array-key, int>|int  $signals
     * @param  callable(int $signal): void  $callback
     * @return void
     */
    public function trap($signals, $callback)
    {
        Signals::whenAvailable(function () use ($signals, $callback) {
            $this->signals ??= new Signals(
                $this->getApplication()->getSignalRegistry(),
            );

            \WPWCore\Collections\collect(Arr::wrap($signals))
                ->each(fn($signal) => $this->signals->register($signal, $callback));
        });
    }

    /**
     * Untrap signal handlers set within the command's handler.
     *
     * @return void
     *
     * @internal
     */
    public function untrap()
    {
        if (! is_null($this->signals)) {
            $this->signals->unregister();

            $this->signals = null;
        }
    }
}
