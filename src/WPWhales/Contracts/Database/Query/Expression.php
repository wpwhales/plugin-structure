<?php

namespace WPWhales\Contracts\Database\Query;

use WPWhales\Database\Grammar;

interface Expression
{
    /**
     * Get the value of the expression.
     *
     * @param  \WPWhales\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar);
}
