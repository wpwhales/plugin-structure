<?php

namespace WPWCore\Database\Query;

use WPWhales\Contracts\Database\Query\Expression as ExpressionContract;
use WPWCore\Database\Grammar;

class Expression implements ExpressionContract
{
    /**
     * The value of the expression.
     *
     * @var string|int|float
     */
    protected $value;

    /**
     * Create a new raw query expression.
     *
     * @param  string|int|float  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value of the expression.
     *
     * @param  \WPWCore\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar)
    {
        return $this->value;
    }
}
