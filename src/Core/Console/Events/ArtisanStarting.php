<?php

namespace WPWCore\Console\Events;

class ArtisanStarting
{
    /**
     * The Artisan application instance.
     *
     * @var \WPWCore\Console\Application
     */
    public $artisan;

    /**
     * Create a new event instance.
     *
     * @param  \WPWCore\Console\Application  $artisan
     * @return void
     */
    public function __construct($artisan)
    {
        $this->artisan = $artisan;
    }
}
