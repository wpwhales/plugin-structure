<?php


trait WP_Plugin_Application
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
        if (!$this->app) {
            $this->refreshApplication();
        }

    }


    public function tear_down()
    {
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
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {


        $app = new WPWCore\Application(
            dirname(__DIR__)
        );


        $app->singleton(WPWhales\Contracts\Debug\ExceptionHandler::class, WPWCore\Exceptions\Handler::class);


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

        /**
         * TODO Will check that after URL integration
         */

//        $url = $this->app->make('config')->get('app.url', 'http://localhost');


        //$this->app->make('url')->forceRootUrl($url);

        $this->app->boot();
    }
}