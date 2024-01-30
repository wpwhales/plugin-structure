<?php

namespace Tests\Commands;


use WPWCore\Database\QueryException;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Contracts\Console\Kernel as ConsoleKernelContract;
use WPWhales\Support\Facades\Artisan;
use function WPWCore\app;

class ArtisanTest extends \WP_UnitTestCase
{

    use InteractsWithConsole;


    public function test_artisan_list_command()
    {

        $this->artisan("list")->assertOk();
        $this->artisan("list")->assertSuccessful();

    }

    public function test_arisan_make_migration()
    {
        $this->artisan("make:migration")
            ->expectsQuestion('What should the migration be named? ' . ' E.g. create_flights_table', "create_test_table")->assertSuccessful();

    }


    public function test_arisan_migrate_install()
    {
        try {
            $this->artisan("migrate:install")->assertSuccessful();
        } catch (QueryException $e) {
            $this->assertStringContainsString("Base table or view already exists", $e->getMessage());
        }
    }

    public function test_arisan_migrate_status()
    {

        $this->artisan("migrate:status")->assertSuccessful();


    }




}
