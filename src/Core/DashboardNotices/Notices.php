<?php

namespace WPWCore\DashboardNotices;


use WPWhales\Container\Container;

class Notices
{

    protected $app;

    protected $notices = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function addNotice(...$parameters)
    {

        $this->notices[] = new Notice(...array_values($parameters));


    }

    public function renderNotices(){

        foreach($this->notices as $notice){
            echo $notice->render();

        }
    }

}
