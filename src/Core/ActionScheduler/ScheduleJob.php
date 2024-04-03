<?php

namespace WPWCore\ActionScheduler;


use WPWCore\Application;

class ScheduleJob
{

    protected $scheduler;

    public function __construct(string $command, ActionScheduler $scheduler)
    {
        $this->command = $command;
        $this->scheduler = $scheduler;
    }

    protected function schedule()
    {

        $this->getScheduler()->schedule_cron(time(), $this->cron,"wpwcore_command_".md5($this->command)."_action",[]);


    }

    protected function getScheduler()
    {

        return $this->scheduler;
    }
}