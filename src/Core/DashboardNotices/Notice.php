<?php

namespace WPWCore\DashboardNotices;


use WPWhales\Container\Container;

class Notice
{

    protected string $title = "";
    protected string $message = "";

    protected bool $dismissible = true;

    protected string $type = 'error';

    public function __construct(string $title = "", string $message = "", bool $dismissible = true,$type='error')
    {

        $this->title = $title;
        $this->message = $message;
        $this->dismissible = $dismissible;
        $this->type = $type;


    }

    public function getMessage(){
        return $this->message;
    }

    public function getTitle(){
        return $this->title;
    }
    public function getDismissible(){
        return $this->dismissible;
    }

    public function getType(){
        return $this->type;
    }

    public function render(){

        $class = $this->getDismissible() ? 'is-dismissible' : '';
        return "<div class='notice notice-{$this->getType()} {$class}'>
        <p>{$this->getMessage()}</p>
    </div>";

    }



}
