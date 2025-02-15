<?php

namespace WPWCore\Dusk\Concerns;

use Facebook\WebDriver\WebDriverKeys;
use WPWhales\Support\Str;
use WPWCore\Dusk\Keyboard;
use function WPWCore\Collections\collect;
trait InteractsWithKeyboard
{
    /**
     * Execute the given callback while interacting with the keyboard.
     *
     * @param  callable(\WPWCore\Dusk\Keyboard):void  $callback
     * @return $this
     */
    public function withKeyboard(callable $callback)
    {
        return tap($this, fn () => $callback(new Keyboard($this)));
    }

    /**
     * Parse the keys before sending to the keyboard.
     *
     * @param  array  $keys
     * @return array
     */
    protected function parseKeys($keys)
    {
        return collect($keys)->map(function ($key) {
            if (is_string($key) && Str::startsWith($key, '{') && Str::endsWith($key, '}')) {
                $key = constant(WebDriverKeys::class.'::'.strtoupper(trim($key, '{}')));
            }

            if (is_array($key) && Str::startsWith($key[0], '{')) {
                $key[0] = constant(WebDriverKeys::class.'::'.strtoupper(trim($key[0], '{}')));
            }

            return $key;
        })->all();
    }
}
