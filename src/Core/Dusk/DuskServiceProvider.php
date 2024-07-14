<?php

namespace WPWCore\Dusk;

use WPWCore\Application;
use WPWhales\Support\Facades\Route;
use WPWhales\Support\ServiceProvider;
use function WPWCore\app;

class DuskServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {

        if (defined("DUSK_TESTING_ENVIRONMENT")) {
            /**
             * @var $app Application
             */
            $app = $this->app;
            $app->createWebRoutesFromFile(__DIR__."/routes.php");

        }


        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\DuskCommand::class,
//                Console\DuskFailsCommand::class,
//                Console\MakeCommand::class,
//                Console\PageCommand::class,
//                Console\PurgeCommand::class,
//                Console\ComponentCommand::class,
                Console\ChromeDriverCommand::class,
            ]);
        }
    }
}
