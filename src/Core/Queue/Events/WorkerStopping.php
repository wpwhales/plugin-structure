<?php

namespace WPWCore\Queue\Events;

class WorkerStopping
{
    /**
     * The worker exit status.
     *
     * @var int
     */
    public $status;

    /**
     * The worker options.
     *
     * @var \WPWCore\Queue\WorkerOptions|null
     */
    public $workerOptions;

    /**
     * Create a new event instance.
     *
     * @param  int  $status
     * @param  \WPWCore\Queue\WorkerOptions|null  $workerOptions
     * @return void
     */
    public function __construct($status = 0, $workerOptions = null)
    {
        $this->status = $status;
        $this->workerOptions = $workerOptions;
    }
}
