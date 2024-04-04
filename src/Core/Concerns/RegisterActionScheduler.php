<?php

namespace WPWCore\Concerns;


use WPWhales\Contracts\Console\Kernel;

trait RegisterActionScheduler
{

    public function withActionScheduler()
    {

        $path = dirname(__FILE__, 4);
        if (file_exists($path . "/woocommerce/action-scheduler/action-scheduler.php")) {
            require $path . "/woocommerce/action-scheduler/action-scheduler.php";
        } else if (file_exists($path . "/vendor/woocommerce/action-scheduler/action-scheduler.php")) {
            require $path . "/vendor/woocommerce/action-scheduler/action-scheduler.php";
        } else if(file_exists($this->basePath("vendor/woocommerce/action-scheduler/action-scheduler.php"))) {
            require $this->basePath("vendor/woocommerce/action-scheduler/action-scheduler.php");

        } else {

            throw new \Exception("Please install the action scheduler . composer require woocommerce/action-scheduler");
        }


        $this->app->singleton(\ActionScheduler::class, function ($app) {
            return new \WPWCore\ActionScheduler\ActionScheduler();
        });

        // Binding using custom strings as aliases
        $this->app->singleton('scheduler', \ActionScheduler::class);
        $this->app->singleton('taskmanager', \ActionScheduler::class);


        $kernel = $this->app[Kernel::class];

        $kernel->registerActionHooks();

    }



}
