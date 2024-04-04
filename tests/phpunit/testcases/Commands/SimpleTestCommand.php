<?php

namespace Tests\Commands;


use WPWCore\Console\Command;
use WPWCore\Database\QueryException;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Contracts\Console\Kernel as ConsoleKernelContract;
use WPWhales\Support\Facades\Artisan;
use WPWhales\Testing\PendingCommand;
use function WPWCore\app;

class SimpleTestCommand extends \WP_UnitTestCase
{

    use InteractsWithConsole;


    public function test_artisan_simple_command()
    {


        $response = $this->artisan("wpwcore:simple");

        $response->assertSuccessful();

        $response->expectsOutput("admin");
        $response->expectsOutput("Hello World!!!");



    }


    /**
     * Call artisan command and return code.
     *
     * @param string $command
     * @param array $parameters
     * @return \WPWhales\Testing\PendingCommand|int
     */
    public function artisan($command, $parameters = [])
    {
        $this->app->singletonIf(Kernel::class, KernelTest2::class);

        if (!$this->mockConsoleOutput) {
            return $this->app[Kernel::class]->call($command, $parameters);
        }

        return new PendingCommand($this, $this->app, $command, $parameters);
    }


}


class KernelTest2 extends \WPWCore\Console\Kernel
{

    protected $commands = [

        SimpleCommand5::class
    ];

}


class SimpleCommand5 extends Command
{

    protected $name = "wpwcore:simple";


    public function handle()
    {

        $user = get_userdata(1);
        $this->info($user->user_login);
        $this->info("Hello World!!!");

    }
}