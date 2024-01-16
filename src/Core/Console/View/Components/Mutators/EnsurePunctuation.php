<?php

namespace WPWCore\Console\View\Components\Mutators;

class EnsurePunctuation
{
    /**
     * Ensures the given string ends with punctuation.
     *
     * @param  string  $string
     * @return string
     */
    public function __invoke($string)
    {
        if (!\WPWCore\Support\str($string)
            ->endsWith(['.', '?', '!', ':'])) {
            return "$string.";
        }

        return $string;
    }
}
