<?php

namespace WPWCore\ActionScheduler;

interface  JobInterface
{

    public function __construct();

    public function handle();

}
