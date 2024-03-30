<?php

namespace WPWCore\Testing;


trait DatabaseMigrations
{

    protected  $artisan;
    /**
     * Run the database migrations for the application.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {

        $this->artisan()->call('migrate');

    }

    protected function artisan(){

        if(empty($this->artisan)){
            $this->artisan = new \WPWCore\Console\Kernel($this->app);
        }


        return $this->artisan;

    }

    public function runMigrateReset(){
        $this->artisan()->call('migrate:reset');
    }
}
