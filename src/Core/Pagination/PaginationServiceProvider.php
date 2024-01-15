<?php

namespace WPWCore\Pagination;

use WPWhales\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pagination');


    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        LengthAwarePaginator::defaultView("pagination::default");

        PaginationState::resolveUsing($this->app);

        $app = $this->app;
        Paginator::currentPageResolver(function ($pageName = 'wpw_page') use ($app) {

            $pageName = "wpw_page";
            $page = $app['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }
}
