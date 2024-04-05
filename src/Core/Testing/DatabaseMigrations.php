<?php

namespace WPWCore\Testing;


use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;

trait DatabaseMigrations
{

    /**
     * Run the database migrations for the application.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {


        return $this->app[Kernel::class]->call("migrate");



    }



    public function runMigrateReset(){
        return $this->app[Kernel::class]->call("migrate:reset");

    }
}
