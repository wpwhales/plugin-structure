<?php

namespace WPWhales\Testing;

use WPWhales\Contracts\Support\DeferrableProvider;
use WPWhales\Support\ServiceProvider;
use WPWhales\Testing\Concerns\TestDatabases;

class ParallelTestingServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use TestDatabases;

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootTestDatabase();
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->app->singleton(ParallelTesting::class, function () {
                return new ParallelTesting($this->app);
            });
        }
    }
}
