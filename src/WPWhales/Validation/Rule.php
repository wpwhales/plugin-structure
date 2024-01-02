<?php

namespace WPWhales\Validation;

use WPWhales\Contracts\Support\Arrayable;
use WPWhales\Support\Traits\Macroable;
use WPWhales\Validation\Rules\Can;
use WPWhales\Validation\Rules\Dimensions;
use WPWhales\Validation\Rules\Enum;
use WPWhales\Validation\Rules\ExcludeIf;
use WPWhales\Validation\Rules\Exists;
use WPWhales\Validation\Rules\File;
use WPWhales\Validation\Rules\ImageFile;
use WPWhales\Validation\Rules\In;
use WPWhales\Validation\Rules\NotIn;
use WPWhales\Validation\Rules\ProhibitedIf;
use WPWhales\Validation\Rules\RequiredIf;
use WPWhales\Validation\Rules\Unique;

class Rule
{
    use Macroable;

    /**
     * Get a can constraint builder instance.
     *
     * @param  string  $ability
     * @param  mixed  ...$arguments
     * @return \WPWhales\Validation\Rules\Can
     */
    public static function can($ability, ...$arguments)
    {
        return new Can($ability, $arguments);
    }

    /**
     * Apply the given rules if the given condition is truthy.
     *
     * @param  callable|bool  $condition
     * @param  array|string|\Closure  $rules
     * @param  array|string|\Closure  $defaultRules
     * @return \WPWhales\Validation\ConditionalRules
     */
    public static function when($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules($condition, $rules, $defaultRules);
    }

    /**
     * Apply the given rules if the given condition is falsy.
     *
     * @param  callable|bool  $condition
     * @param  array|string|\Closure  $rules
     * @param  array|string|\Closure  $defaultRules
     * @return \WPWhales\Validation\ConditionalRules
     */
    public static function unless($condition, $rules, $defaultRules = [])
    {
        return new ConditionalRules(! $condition, $rules, $defaultRules);
    }

    /**
     * Create a new nested rule set.
     *
     * @param  callable  $callback
     * @return \WPWhales\Validation\NestedRules
     */
    public static function forEach($callback)
    {
        return new NestedRules($callback);
    }

    /**
     * Get a unique constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \WPWhales\Validation\Rules\Unique
     */
    public static function unique($table, $column = 'NULL')
    {
        return new Unique($table, $column);
    }

    /**
     * Get an exists constraint builder instance.
     *
     * @param  string  $table
     * @param  string  $column
     * @return \WPWhales\Validation\Rules\Exists
     */
    public static function exists($table, $column = 'NULL')
    {
        return new Exists($table, $column);
    }

    /**
     * Get an in constraint builder instance.
     *
     * @param  \WPWhales\Contracts\Support\Arrayable|array|string  $values
     * @return \WPWhales\Validation\Rules\In
     */
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new In(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a not_in constraint builder instance.
     *
     * @param  \WPWhales\Contracts\Support\Arrayable|array|string  $values
     * @return \WPWhales\Validation\Rules\NotIn
     */
    public static function notIn($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return new NotIn(is_array($values) ? $values : func_get_args());
    }

    /**
     * Get a required_if constraint builder instance.
     *
     * @param  callable|bool  $callback
     * @return \WPWhales\Validation\Rules\RequiredIf
     */
    public static function requiredIf($callback)
    {
        return new RequiredIf($callback);
    }

    /**
     * Get a exclude_if constraint builder instance.
     *
     * @param  callable|bool  $callback
     * @return \WPWhales\Validation\Rules\ExcludeIf
     */
    public static function excludeIf($callback)
    {
        return new ExcludeIf($callback);
    }

    /**
     * Get a prohibited_if constraint builder instance.
     *
     * @param  callable|bool  $callback
     * @return \WPWhales\Validation\Rules\ProhibitedIf
     */
    public static function prohibitedIf($callback)
    {
        return new ProhibitedIf($callback);
    }

    /**
     * Get an enum constraint builder instance.
     *
     * @param  string  $type
     * @return \WPWhales\Validation\Rules\Enum
     */
    public static function enum($type)
    {
        return new Enum($type);
    }

    /**
     * Get a file constraint builder instance.
     *
     * @return \WPWhales\Validation\Rules\File
     */
    public static function file()
    {
        return new File;
    }

    /**
     * Get an image file constraint builder instance.
     *
     * @return \WPWhales\Validation\Rules\ImageFile
     */
    public static function imageFile()
    {
        return new ImageFile;
    }

    /**
     * Get a dimensions constraint builder instance.
     *
     * @param  array  $constraints
     * @return \WPWhales\Validation\Rules\Dimensions
     */
    public static function dimensions(array $constraints = [])
    {
        return new Dimensions($constraints);
    }
}
