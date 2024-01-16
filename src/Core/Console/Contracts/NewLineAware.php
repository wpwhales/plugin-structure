<?php

namespace WPWCore\Console\Contracts;

interface NewLineAware
{
    /**
     * Whether a newline has already been written.
     *
     * @return bool
     */
    public function newLineWritten();
}
