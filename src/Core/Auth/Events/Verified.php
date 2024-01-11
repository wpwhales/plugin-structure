<?php

namespace WPWCore\Auth\Events;

use WPWhales\Queue\SerializesModels;

class Verified
{
    use SerializesModels;

    /**
     * The verified user.
     *
     * @var \WPWhales\Contracts\Auth\MustVerifyEmail
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Contracts\Auth\MustVerifyEmail  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
