<?php

namespace Tests\ActionScheduler;


use WPWCore\ActionScheduler\ActionScheduler;
use WPWCore\Application;
use WPWCore\Console\Command;
use WPWhales\Console\Scheduling\Schedule;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Support\Facades\DB;
use function WPWCore\app;


class IntegrationTest extends \WP_UnitTestCase
{


    public function set_up()
    {
        parent::set_up();
        $this->app->singleton(Kernel::class, function () {
            return new KernelTest($this->app);
        });
    }



    public function test_scheduler_initialization()
    {


        $this->app->withActionScheduler();

        $this->assertInstanceOf(ActionScheduler::class, $this->app["scheduler"]);

        $this->assertInstanceOf(\ActionScheduler_ActionFactory::class, $this->app["scheduler"]->factory());

    }

    public function test_hook_is_registered_for_a_command()
    {

        $this->app->withActionScheduler();


        $this->assertTrue(has_action("wpwcore_command_" . md5(SimpleCommand::class) . "_action"));

        do_action("wpwcore_command_" . md5(SimpleCommand::class) . "_action");

        $output = $this->app[Kernel::class]->output();

        $this->assertStringContainsString("Hello World!!!",$output);
        $this->assertStringContainsString("admin",$output);


    }


    public function test_schedule_command(){
        $this->app->withActionScheduler();


        /**
         * @var $scheduler ActionScheduler
         */
        $scheduler = $this->app["scheduler"];
        $action_id =  $scheduler->schedule_command(SimpleCommand::class)->everyHour();


        $schedule = \ActionScheduler_DBStore::instance()->fetch_action($action_id)->get_schedule();

        $this->assertTrue($schedule->is_recurring());
        $this->assertStringContainsString("0 * * * *",$schedule->get_recurrence());
        DB::table("actionscheduler_actions")->where("action_id",$action_id)->delete();
    }


    public function test_schedule_a_command_with_change_in_time(){
        $this->app->withActionScheduler();


        /**
         * @var $scheduler ActionScheduler
         */
        $scheduler = $this->app["scheduler"];
        $action_id =  $scheduler->schedule_command(SimpleCommand2::class)->everyHour();


        $schedule = \ActionScheduler_DBStore::instance()->fetch_action($action_id)->get_schedule();

        $this->assertTrue($schedule->is_recurring());

        $this->assertStringContainsString("0 * * * *",$schedule->get_recurrence());

        $action_id =  $scheduler->schedule_command(SimpleCommand2::class)->everyMinute();


        $schedule = \ActionScheduler_DBStore::instance()->fetch_action($action_id)->get_schedule();

        $this->assertTrue($schedule->is_recurring());
        $this->assertStringContainsString("* * * * *",$schedule->get_recurrence());
    }








}


class KernelTest extends \WPWCore\Console\Kernel
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

    protected $commands = [

        SimpleCommand::class,
        SimpleCommand2::class,
        SimpleCommand3::class
    ];




}


class SimpleCommand2 extends Command
{

    protected $name = "wpwcore:simple2";


    public function handle()
    {


        $this->info("command2");

    }
}
class SimpleCommand3 extends Command
{

    protected $name = "wpwcore:simple3";


    public function handle()
    {


        $this->info("command3");

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
