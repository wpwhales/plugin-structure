<?php

namespace Tests\Routing;


trait TraitRoutingHandler
{


    protected function loadRoutes()
    {

        $this->app->createWebRoutesFromFile(__DIR__ . "/routes/web.php");
        $this->app->createAjaxRoutesFromFile(__DIR__ . "/routes/ajax.php");

    }

}

