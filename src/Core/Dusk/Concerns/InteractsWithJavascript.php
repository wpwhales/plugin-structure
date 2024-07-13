<?php

namespace WPWCore\Dusk\Concerns;
use function WPWCore\Collections\collect;
trait InteractsWithJavascript
{
    /**
     * Execute JavaScript within the browser.
     *
     * @param  string|array  $scripts
     * @return array
     */
    public function script($scripts)
    {
        return collect((array) $scripts)->map(function ($script) {
            return $this->driver->executeScript($script);
        })->all();
    }
}
