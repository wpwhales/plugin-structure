<?php

namespace WPWhales\Contracts\Database\Query;

use WPWCore\Database\Grammar;

interface Expression
{
    /**
     * Get the value of the expression.
     *
     * @param  \WPWCore\Database\Grammar  $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar);
}
