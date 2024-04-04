<?php

namespace Tests\ActionScheduler;


use WPWCore\ActionScheduler\ActionScheduler;
use WPWCore\Application;
use WPWCore\Bus\PendingDispatch;
use WPWCore\Bus\Queueable;
use WPWCore\Console\Command;
use WPWCore\Models\User;
use WPWCore\Queue\InteractsWithQueue;
use WPWCore\Queue\SerializesModels;
use WPWhales\Console\Scheduling\Schedule;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Contracts\Queue\ShouldQueue;
use WPWhales\Support\Facades\DB;
use function WPWCore\app;


class JobsTest extends \WP_UnitTestCase
{


    public function set_up()
    {
        parent::set_up();
        $this->app->singleton(Kernel::class, function () {
            return new KernelTestClass($this->app);
        });
    }


    public function test_scheduler_initialization()
    {


        $this->app->withActionScheduler();
        $x = new PendingDispatch(new Job(1));

    }


}

class Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $user_id;

    /**
     * Create a new job instance.
     */
    public function __construct($user_id)
    {

        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $user= User::find($this->user_id);
        error_log("This is a message from the job ".$user->display_name);


    }
}

class KernelTestClass extends \WPWCore\Console\Kernel
{

    public function __construct(Application $app)
    {
        $this->app = $app;

        if ($this->app->runningInConsole()) {
            $this->setRequestForConsole($this->app);
        } else {
            $this->rerouteSymfonyCommandEvents();
        }


    }


}

