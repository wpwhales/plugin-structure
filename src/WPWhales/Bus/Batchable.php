<?php

namespace WPWhales\Bus;

use Carbon\CarbonImmutable;
use WPWhales\Container\Container;
use WPWhales\Support\Str;
use WPWhales\Support\Testing\Fakes\BatchFake;

trait Batchable
{
    /**
     * The batch ID (if applicable).
     *
     * @var string
     */
    public $batchId;

    /**
     * The fake batch, if applicable.
     *
     * @var \WPWhales\Support\Testing\Fakes\BatchFake
     */
    private $fakeBatch;

    /**
     * Get the batch instance for the job, if applicable.
     *
     * @return \WPWhales\Bus\Batch|null
     */
    public function batch()
    {
        if ($this->fakeBatch) {
            return $this->fakeBatch;
        }

        if ($this->batchId) {
            return Container::getInstance()->make(BatchRepository::class)->find($this->batchId);
        }
    }

    /**
     * Determine if the batch is still active and processing.
     *
     * @return bool
     */
    public function batching()
    {
        $batch = $this->batch();

        return $batch && ! $batch->cancelled();
    }

    /**
     * Set the batch ID on the job.
     *
     * @param  string  $batchId
     * @return $this
     */
    public function withBatchId(string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Indicate that the job should use a fake batch.
     *
     * @param  string  $id
     * @param  string  $name
     * @param  int  $totalJobs
     * @param  int  $pendingJobs
     * @param  int  $failedJobs
     * @param  array  $failedJobIds
     * @param  array  $options
     * @param  \Carbon\CarbonImmutable  $createdAt
     * @param  \Carbon\CarbonImmutable|null  $cancelledAt
     * @param  \Carbon\CarbonImmutable|null  $finishedAt
     * @return array{0: $this, 1: \WPWhales\Support\Testing\Fakes\BatchFake}
     */
    public function withFakeBatch(string $id = '',
                                  string $name = '',
                                  int $totalJobs = 0,
                                  int $pendingJobs = 0,
                                  int $failedJobs = 0,
                                  array $failedJobIds = [],
                                  array $options = [],
                                  CarbonImmutable $createdAt = null,
                                  ?CarbonImmutable $cancelledAt = null,
                                  ?CarbonImmutable $finishedAt = null)
    {
        $this->fakeBatch = new BatchFake(
            empty($id) ? (string) Str::uuid() : $id,
            $name,
            $totalJobs,
            $pendingJobs,
            $failedJobs,
            $failedJobIds,
            $options,
            $createdAt ?? CarbonImmutable::now(),
            $cancelledAt,
            $finishedAt,
        );

        return [$this, $this->fakeBatch];
    }
}
