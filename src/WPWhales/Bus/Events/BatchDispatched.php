<?php

namespace WPWhales\Bus\Events;

use WPWhales\Bus\Batch;

class BatchDispatched
{
    /**
     * The batch instance.
     *
     * @var \WPWhales\Bus\Batch
     */
    public $batch;

    /**
     * Create a new event instance.
     *
     * @param  \WPWhales\Bus\Batch  $batch
     * @return void
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }
}
