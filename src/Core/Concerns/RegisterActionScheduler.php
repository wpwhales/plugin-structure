<?php

namespace WPWCore\Concerns;


use WPWCore\ActionScheduler\ActionScheduler;
use WPWCore\ActionScheduler\QueueWorker;
use WPWCore\Queue\QueueServiceProvider;
use WPWCore\Queue\WorkerOptions;
use WPWhales\Bus\BusServiceProvider;
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
        } else if (file_exists($this->basePath("vendor/woocommerce/action-scheduler/action-scheduler.php"))) {
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

        $this->configure("queue");
        $this->register(QueueServiceProvider::class);

        $this->register(BusServiceProvider::class);

        $kernel = $this->app[Kernel::class];

        $kernel->registerActionHooks();

        add_action("action_scheduler_init", function () {
            /**
             * @var $scheduler ActionScheduler
             */
            $scheduler = $this->app->make("scheduler");
            $scheduler->schedule_recurring(time(), 120, "wpwcore_schedule_jobs_processing");

        });
        add_action("wpwcore_schedule_jobs_processing", function () {
            $worker = $this->make("queue.worker");

            $options = (new WorkerOptions());
            $options->maxTries = 2;
            $worker->wpwcoreWorker("database", "", $options);

        });
    }


}
