<?php

namespace WPWCore\Testing;

trait DatabaseMigrations
{
    /**
     * Run the database migrations for the application.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        $artisan = new \WPWCore\Console\Kernel($this->app);

        $artisan->call('migrate:reset');
        $artisan->call('migrate');

    }
}
