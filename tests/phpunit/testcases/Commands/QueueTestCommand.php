<?php

namespace Tests\Commands;


use WPWCore\Auth\AuthenticationException;
use WPWCore\Bus\Queueable;
use WPWCore\Console\Command;
use WPWCore\Database\QueryException;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Queue\InteractsWithQueue;
use WPWCore\Queue\SerializesModels;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;

use WPWhales\Contracts\Queue\ShouldQueue;
use WPWhales\Support\Facades\Artisan;
use WPWhales\Support\Facades\DB;
use function WPWCore\app;
use function WPWCore\dispatch;

class QueueTestCommand extends \WP_UnitTestCase
{

    use InteractsWithConsole;

    public function set_up()
    {
        parent::set_up();
        $this->app->singleton(Kernel::class, function () {
            return new KernelTestNew($this->app);
        });

        $this->app->prepareForConsoleCommand(true);

        $this->app->withActionScheduler();
        $this->app[Kernel::class]->call("queue:table");
        $this->app[Kernel::class]->call("queue:failed-table");
        $this->app[Kernel::class]->call("migrate");


    }


    public function test_queue_list_command()
    {

        $this->app->withActionScheduler();
        DB::table("wpwcore_failed_jobs")->delete();

        dispatch(new FailedJob());

        do_action("wpwcore_schedule_jobs_processing");


//        $this->withoutMockingConsoleOutput();
        $response = $this->artisan("queue:failed");

//        dd($this->app[Kernel::class]->output());

        $response->expectsOutputToContain("something wrong");


    }

    public function test_queue_failed_requeue_command()
    {

        $this->app->withActionScheduler();
        DB::table("wpwcore_failed_jobs")->delete();
        DB::table("wpwcore_jobs")->delete();

        delete_option(md5(FailedJobOnCondition::class));
        delete_option("something_is_unique_is_true_now");

        //dispatch a job
        dispatch(new FailedJobOnCondition());

        $job = DB::table("wpwcore_jobs")->first();
        $uuid = json_decode($job->payload)->uuid;

        //run the job queue
        do_action("wpwcore_schedule_jobs_processing");

        //job should be in failed table now
        $this->seeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);


        $this->withoutMockingConsoleOutput();
        //let's try so that it gets populated in jobs table now again;
        $response = $this->artisan("queue:retry all");

        $job = DB::table("wpwcore_jobs")->first();


        $this->assertEquals(json_decode($job->payload)->uuid, $uuid);

        $this->notSeeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);

        //now let's make sure the job run after the fix
        $this->assertFalse(get_option("something_is_unique_is_true_now"));

        add_option(md5(FailedJobOnCondition::class), time());
        do_action("wpwcore_schedule_jobs_processing");

        $this->assertTrue(get_option("something_is_unique_is_true_now"));

    }


    public function test_queue_failed_requeue_specific_job_command()
    {

        $this->app->withActionScheduler();
        DB::table("wpwcore_failed_jobs")->delete();
        DB::table("wpwcore_jobs")->delete();

        delete_option(md5(FailedJobOnCondition::class));
        delete_option("something_is_unique_is_true_now");

        //dispatch a job
        dispatch(new FailedJobOnCondition());
        dispatch(new FailedJobOnCondition());

        $job = DB::table("wpwcore_jobs")->first();
        $uuid = json_decode($job->payload)->uuid;

        //run the job queue
        do_action("wpwcore_schedule_jobs_processing");

        //job should be in failed table now
        $this->seeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);

        $this->assertEquals(DB::table("wpwcore_failed_jobs")->count(),2);

        $this->withoutMockingConsoleOutput();
        //let's try so that it gets populated in jobs table now again;
        $response = $this->artisan("queue:retry ".$uuid);

        $job = DB::table("wpwcore_jobs")->first();


        $this->assertEquals(json_decode($job->payload)->uuid, $uuid);

        $this->notSeeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);

        //now let's make sure the job run after the fix
        $this->assertFalse(get_option("something_is_unique_is_true_now"));

        add_option(md5(FailedJobOnCondition::class), time());
        do_action("wpwcore_schedule_jobs_processing");

        $this->assertTrue(get_option("something_is_unique_is_true_now"));

    }

    public function test_flush_queue_command()
    {
        $this->app->withActionScheduler();
        DB::table("wpwcore_failed_jobs")->delete();
        DB::table("wpwcore_jobs")->delete();
        dispatch(new FailedJob());

        $job = DB::table("wpwcore_jobs")->first();
        $uuid = json_decode($job->payload)->uuid;

        //run the job queue
        do_action("wpwcore_schedule_jobs_processing");

        $this->seeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);


        $this->withoutMockingConsoleOutput();

        $response = $this->artisan("queue:flush");

        $this->notSeeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);


    }


    public function test_forget_failed_queue_command(){
        $this->app->withActionScheduler();
        DB::table("wpwcore_failed_jobs")->delete();
        DB::table("wpwcore_jobs")->delete();
        dispatch(new FailedJob());

        $job = DB::table("wpwcore_jobs")->first();
        $uuid = json_decode($job->payload)->uuid;

        //run the job queue
        do_action("wpwcore_schedule_jobs_processing");

        $this->seeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);


        $this->withoutMockingConsoleOutput();

        $response = $this->artisan("queue:forget ".$uuid);


        $this->notSeeInDatabase("wpwcore_failed_jobs", [
            "uuid" => $uuid
        ]);


    }


}

class FailedJobOnCondition implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct()
    {


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $condition = get_option(md5(self::class));

        if ($condition) {

            add_option("something_is_unique_is_true_now", true);
            return;
        }

        throw new \Exception("Failed Job");

    }
}


class FailedJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct()
    {


    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        throw new \Exception("something wrong");

    }
}


class KernelTestNew extends \WPWCore\Console\Kernel
{

    protected $commands = [

    ];


    public function scheduleEvents()
    {

    }


}