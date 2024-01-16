<?php

namespace WPWCore\Database\Eloquent\Casts;

use WPWhales\Contracts\Database\Eloquent\Castable;
use WPWhales\Contracts\Database\Eloquent\CastsAttributes;
use WPWhales\Support\Str;

class AsStringable implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return \WPWhales\Contracts\Database\Eloquent\CastsAttributes<\WPWhales\Support\Stringable, string|\Stringable>
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return isset($value) ? Str::of($value) : null;
            }

            public function set($model, $key, $value, $attributes)
            {
                return isset($value) ? (string) $value : null;
            }
        };
    }
}
