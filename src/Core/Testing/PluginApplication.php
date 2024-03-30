<?php


namespace WPWCore\Testing;

trait PluginApplication
{

    /**
     * The application instance.
     *
     * @var \WPWCore\Application
     */
    protected $app;


    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    protected $beforeApplicationDestroyedCallbacks = [];


    public function set_up()
    {
        parent::set_up();
        if(get_parent_class($this) === \WP_Ajax_UnitTestCase::class){
            add_filter( 'wp_doing_ajax', '__return_true' );

            set_current_screen( 'ajax' );
        }
        if (!$this->app) {
            $this->refreshApplication();
        }


        if(method_exists($this,"runDatabaseMigrations")){


            $this->runDatabaseMigrations();
        }

    }

    public static function tear_down_after_class()
    {

        parent::tear_down_after_class(); // TODO: Change the autogenerated stub
        $directoryPath = WP_CONTENT_DIR."/wpwhales";
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


    public function tear_down()
    {
        if(method_exists($this,"runMigrateReset")){
            $this->runMigrateReset();
        }
        parent::tear_down();
        if ($this->app) {
            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
                $callback();
            }

            $this->app->flush();
            $this->app = null;
        }

        /**
         * TODO Will check that after view integration
         */
//        \WPWhales\View\Component::flushCache();
//        \WPWhales\View\Component::forgetComponentsResolver();
//        \WPWhales\View\Component::forgetFactory();
    }

    /**
     * Creates the application.
     *
     * @return \WPWCore\Application
     */
    public function createApplication()
    {


        $app = new \WPWCore\Application(
            dirname(__DIR__)
        );


        $app->singleton(\WPWhales\Contracts\Debug\ExceptionHandler::class, \WPWCore\Exceptions\Handler::class);

        $app->withFacades();
        $app->withEloquent();

        return $app;
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        \WPWhales\Support\Facades\Facade::clearResolvedInstances();

        $this->app = $this->createApplication();

        if(method_exists($this,"runDatabaseMigrations")){
            $this->runDatabaseMigrations();
        }

        /**
         * TODO Will check that after URL integration
         */

//        $url = $this->app->make('config')->get('app.url', 'http://localhost');


        //$this->app->make('url')->forceRootUrl($url);

        $this->app->boot();
    }


    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $onConnection
     * @return $this
     */
    protected function seeInDatabase($table, array $data, $onConnection = null)
    {
        $count = $this->app->make('db')->connection($onConnection)->table($table)->where($data)->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $onConnection
     * @return $this
     */
    protected function missingFromDatabase($table, array $data, $onConnection = null)
    {
        return $this->notSeeInDatabase($table, $data, $onConnection);
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string|null  $onConnection
     * @return $this
     */
    protected function notSeeInDatabase($table, array $data, $onConnection = null)
    {
        $count = $this->app->make('db')->connection($onConnection)->table($table)->where($data)->count();

        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

}