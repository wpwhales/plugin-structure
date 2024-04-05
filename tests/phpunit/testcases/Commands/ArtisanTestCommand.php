<?php

namespace Tests\Commands;


use WPWCore\Database\QueryException;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Testing\Concerns\InteractsWithConsole;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Contracts\Console\Kernel as ConsoleKernelContract;
use WPWhales\Support\Facades\Artisan;
use function WPWCore\app;

class ArtisanTestCommand extends \WP_UnitTestCase
{

    use InteractsWithConsole;



    public static function tear_down_after_class()
    {


        parent::tear_down_after_class();


        $directoryPath = dirname(__DIR__,4)."/src/Core/database";


        if (file_exists($directoryPath)) {
            // Remove files or directories
            // Example: \RecursiveDirectoryIterator::CURRENT_AS_SELF ensures the iterator will delete the root directory itself
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                $item->isDir() ? rmdir($item) : unlink($item);
            }
            rmdir($directoryPath);
        }


    }

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