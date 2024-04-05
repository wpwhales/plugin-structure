<?php

namespace WPWCore\ActionScheduler;

use WPWCore\Queue\Events\JobFailed;
use WPWCore\Queue\Events\JobProcessed;
use WPWCore\Queue\Events\JobProcessing;
use WPWCore\Queue\Events\JobReleasedAfterException;
use WPWCore\Queue\WorkerOptions;
use WPWhales\Contracts\Queue\Job;
use WPWhales\Support\Facades\Log;
use function WPWCore\app;

class QueueWorker extends \WPWCore\Queue\Worker
{

    public function wpwcoreWorker($connectionName, $queue, WorkerOptions $options)
    {

        // First, we will attempt to get the next job off of the queue. We will also
        // register the timeout handler and reset the alarm for this job so it is
        // not stuck in a frozen state forever. Then, we can fire off this job.

        $this->listenForEvents();
        while (true) {
            $job = $this->getNextJob(
                $this->manager->connection($connectionName), $queue
            );


            if ($job) {

                $this->runJob($job, $connectionName, $options);

                if ($options->rest > 0) {
                    $this->sleep($options->rest);
                }
            } else {
                break;
            }
        }


    }


    /**
     * Store a failed job event.
     *
     * @param \WPWCore\Queue\Events\JobFailed $event
     * @return void
     */
    protected function logFailedJob(\WPWCore\Queue\Events\JobFailed $event)
    {
        app("queue.failer")->log(
            $event->connectionName,
            $event->job->getQueue(),
            $event->job->getRawBody(),
            $event->exception
        );
    }


    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        $this->events->listen(JobProcessing::class, function ($event) {
            $this->writeOutput($event, 'starting');
        });

        $this->events->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event, 'success');
        });

        $this->events->listen(JobReleasedAfterException::class, function ($event) {
            $this->writeOutput($event, 'released_after_exception');
        });

        $this->events->listen(JobFailed::class, function ($event) {
            $this->writeOutput($event, 'failed');

            $this->logFailedJob($event);
        });
    }

    /**
     * Write the status output for the queue worker.
     *
     * @param Object $event
     * @param string $status
     * @return void
     */
    protected function writeOutput($event, $status)
    {

        /**
         * @var $job Job
         */
        $job = $event->job;

        Log::info($job->resolveName()." - ".$status);

    }

}