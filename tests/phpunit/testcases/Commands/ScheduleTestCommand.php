<?php

namespace Tests\Commands;


use WPWCore\Console\Command;
use WPWCore\Database\QueryException;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;

use WPWhales\Support\Facades\Artisan;
use function WPWCore\app;

class ScheduleTestCommand extends \WP_UnitTestCase
{

    use InteractsWithConsole;

    public function set_up()
    {
        parent::set_up();
        $this->app->singleton(Kernel::class, function () {
            return new KernelTest($this->app);
        });
    }

    public function test_schedule_refresh_command()
    {

        $this->app->withActionScheduler();

        $response = $this->artisan("schedule:refresh");

        $response->assertSuccessful();
        $response->expectsOutput("Action Schedule events refreshed");
    }

    public function test_schedule_remove_canceled_hooks()
    {
        $this->app->withActionScheduler();


        $response = $this->artisan("schedule:remove-canceled");
        $response->assertSuccessful();

        $response->expectsOutput("Canceled action/hook are cleared from the table");
    }


}


class KernelTest extends \WPWCore\Console\Kernel
{

    protected $commands = [

        SimpleCommand::class
    ];


    public function scheduleEvents()
    {
        $this->app["scheduler"]->schedule_command(SimpleCommand::class)->everyHour();

    }


}


class SimpleCommand extends Command
{

    protected $name = "wpwcore:simple";


    public function handle()
    {

        $user = get_userdata(1);
        $this->info($user->user_login);
        $this->info("Hello World!!!");

    }
}
