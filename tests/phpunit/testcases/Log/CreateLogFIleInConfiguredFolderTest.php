<?php

namespace Tests\Log;


use WPWhales\Support\Facades\Config;
use WPWhales\Support\Facades\Log;

class CreateLogFIleInConfiguredFolderTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();
        $this->app->configure("logging");

        Config::set("logging.channels.daily.path", __DIR__ . "/logs/wpwcore.log");


    }

    public static function tear_down_after_class()
    {
        $directoryPath = __DIR__."/logs";


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

    public function test_log_creates()
    {


        Log::error("hello world");
        $this->assertTrue(file_exists(__DIR__ . "/logs/wpwcore-" . date("Y-m-d", time()) . ".log"));

    }

}



