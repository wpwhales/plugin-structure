<?php

namespace WPWCore\ActionScheduler;


use WPWCore\Application;

class ScheduleCommand
{

    use Schedules;

    protected $command;
    protected $scheduler;

    public function __construct(string $command, ActionScheduler $scheduler)
    {
        $this->command = $command;
        $this->scheduler = $scheduler;
    }

    protected function schedule()
    {

        return $this->getScheduler()->schedule_cron(time(), $this->cron,"wpwcore_command_".md5($this->command)."_action",[]);


    }

    protected function getScheduler()
    {

        return $this->scheduler;
    }
}