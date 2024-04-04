<?php

namespace WPWCore\ActionScheduler;

trait  Schedules {

    protected   $cron; 
    public  function everyMinute() {
        $this->cron =  "* * * * *";
        return $this->schedule();
    }

    public  function everyFiveMinutes() {
        $this->cron =  "*/5 * * * *";
        return $this->schedule();
    }

    public  function everyTenMinutes() {
        $this->cron =  "*/10 * * * *";
        return $this->schedule();
    }

    public  function everyFifteenMinutes() {
        $this->cron =  "*/15 * * * *";
        return $this->schedule();
    }

    public  function everyThirtyMinutes() {
        $this->cron =  "*/30 * * * *";
        return $this->schedule();
    }

    public  function everyHour() {
        $this->cron =  "0 * * * *";
        return $this->schedule();
    }

    public  function everyFiveHours() {
        $this->cron =  "0 */5 * * *";
        return $this->schedule();
    }

    public  function everyTenHours() {
        $this->cron =  "0 */10 * * *";
        return $this->schedule();
    }

    public  function daily() {
        $this->cron =  "0 0 * * *";
        return $this->schedule();
    }

    public  function twiceDaily() {
        $this->cron =  "0 0,12 * * *";
        return $this->schedule();
    }

    public  function everyDays($days) {
        if ($days <= 0) {
            $days = 1;
        }
        $this->cron =  "0 0 */$days * *";
        return $this->schedule();
    }

    public  function everyHours($hours) {
        if ($hours <= 0) {
            $hours = 1;
        }
        $this->cron =  "0 */$hours * * *";
        return $this->schedule();
    }

    public  function everyMinutes($minutes) {
        if ($minutes <= 0) {
            $minutes = 1;
        }
        $this->cron =  "*/$minutes * * * *";
        return $this->schedule();
    }
}

