<?php

namespace WPWhales\Contracts\Auth;

interface PasswordBrokerFactory
{
    /**
     * Get a password broker instance by name.
     *
     * @param  string|null  $name
     * @return \WPWhales\Contracts\Auth\PasswordBroker
     */
    public function broker($name = null);
}
