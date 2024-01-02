<?php

namespace WPWhales\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \WPWhales\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
