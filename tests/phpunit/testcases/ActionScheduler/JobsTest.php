<?php

namespace Tests\ActionScheduler;


use WPWCore\ActionScheduler\ActionScheduler;
use WPWCore\Application;
use WPWCore\Bus\PendingDispatch;
use WPWCore\Bus\Queueable;
use WPWCore\Console\Command;
use WPWCore\Console\ConsoleServiceProvider;
use WPWCore\Database\MigrationServiceProvider;
use WPWCore\Models\User;
use WPWCore\Queue\Console\FailedTableCommand;
use WPWCore\Queue\Console\TableCommand;
use WPWCore\Queue\Events\JobFailed;
use WPWCore\Queue\InteractsWithQueue;
use WPWCore\Queue\SerializesModels;
use WPWCore\Queue\Worker;
use WPWCore\Queue\WorkerOptions;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWCore\Testing\DatabaseMigrations;
use WPWhales\Console\Scheduling\Schedule;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Contracts\Queue\ShouldQueue;
use WPWhales\Support\Facades\DB;
use WPWhales\Support\Facades\Log;
use WPWhales\Support\Facades\Queue;
use function WPWCore\app;
use function WPWCore\dispatch;


class JobsTest extends \WP_UnitTestCase
{

    use InteractsWithConsole, DatabaseMigrations;

    public function runDatabaseMigrations()
    {

        $this->app->singleton(Kernel::class, function () {
            return new KernelTestClass($this->app);
        });

        $this->app->prepareForConsoleCommand(true);

        $this->app[Kernel::class]->call("migrate");


    }

    public function set_up()
    {

        parent::set_up();
        $this->app->withActionScheduler();





    }


    public function test_jobs_watcher_action_scheduler_working(){

        $this->assertTrue(as_has_scheduled_action("wpwcore_schedule_jobs_processing"));
        $this->assertIsNumeric(as_next_scheduled_action("wpwcore_schedule_jobs_processing"));
    }

    public function test_job_exists_using_table()
    {
        $this->app[Kernel::class]->call("queue:table");
        $this->app[Kernel::class]->call("queue:failed-table");
        $this->app[Kernel::class]->call("migrate");
        dispatch(new Job(1));

        $jobs = DB::table("wpwcore_jobs")->get();
        $foundJob = new \stdClass ();
        foreach($jobs as $job){
            $job = json_decode($job->payload);
            $job->data->command = unserialize($job->data->command);
           if(is_a($job->data->command,Job::class) && $job->data->command->user_id ===1){
               $foundJob = $job->data->command;
               break;
           }
        }

        $this->assertEquals($foundJob->user_id,1);
        $this->assertInstanceOf(Job::class,$foundJob);
    }

    public function test_job_exists_using_fake()
    {
        Queue::fake();


        dispatch(new Job(1));
        Queue::assertPushed(function (Job $job, $queue, $data) {

            return $job->user_id === 1;

        });

    }


    public function test_run_job_via_action(){

        $time = time();
        dispatch(new Job(1,$time));


        do_action("wpwcore_schedule_jobs_processing");


        $this->seeInDatabase("users",[
            "user_login"=>"xyz".$time,
            "user_email"=>"xyz".$time."@test.com"
        ]);
    }


}

class FailedJob implements ShouldQueue
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

        throw new \Exception(123);


    }
}

class Job implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    private $time;

    /**
     * Create a new job instance.
     */
    public function __construct($user_id,$time = 111)
    {

        $this->user_id = $user_id;
        $this->time = $time;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $x = wp_create_user("xyz".$this->time,"12344321","xyz".$this->time."@test.com");

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

