<?php


namespace WPWCore\Hooks;


interface ShortCodeInterface
{


    public function render():String;


    public function getName():String;


}