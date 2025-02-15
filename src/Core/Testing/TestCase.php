<?php

namespace WPWCore\Testing;

use Exception;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;
use WPWCore\Console\Application;
use WPWCore\Dusk\DuskServiceProvider;
use WPWCore\View\Component;
use WPWhales\Contracts\Auth\Authenticatable;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Support\Facades\Facade;

abstract class TestCase extends BaseTestCase
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



    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        if (!$this->app) {
            $this->refreshApplication();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

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
        \WPWCore\View\Component::flushCache();
        \WPWCore\View\Component::forgetComponentsResolver();
        \WPWCore\View\Component::forgetFactory();

        Application::forgetBootstrappers();
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

        $app->register(DuskServiceProvider::class);
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

        $this->app->boot();
    }
}
