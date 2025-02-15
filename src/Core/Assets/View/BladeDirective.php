<?php

namespace WPWCore\Assets\View;

class BladeDirective
{
    /**
     * Invoke the @asset directive.
     *
     * @param  string $expression
     * @return string
     */
    public function __invoke($expression)
    {
        return sprintf("<?= %s(%s); ?>", '\WPWCore\asset', $expression);
    }
}
