<?php

namespace Tests\ActionScheduler;


use WPWCore\ActionScheduler\ActionScheduler;
use WPWCore\Console\Command;
use WPWhales\Contracts\Console\Kernel;
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
        $scheduler = $scheduler->schedule_command(SimpleCommand::class)->everyHour();

        $schedule = \ActionScheduler_DBStore::instance()->fetch_action(3)->get_schedule();
        $this->assertTrue($schedule->is_recurring());
        $this->assertStringContainsString("0 * * * *",$schedule->get_recurrence());
    }




}


class KernelTest extends \WPWCore\Console\Kernel
{

    protected $commands = [

        SimpleCommand::class
    ];




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
