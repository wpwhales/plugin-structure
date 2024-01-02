<?php

namespace WPWhales\Contracts\Mail;

interface Factory
{
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Mail\Mailer
     */
    public function mailer($name = null);
}
