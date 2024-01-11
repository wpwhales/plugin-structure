<?php

namespace WPWCore\Auth\Events;

use WPWhales\Queue\SerializesModels;

class Registered
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \WPWhales\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
