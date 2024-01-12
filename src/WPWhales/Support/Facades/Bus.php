<?php

namespace WPWhales\Support\Facades;

use WPWhales\Bus\BatchRepository;
use WPWhales\Contracts\Bus\Dispatcher as BusDispatcherContract;
use WPWhales\Foundation\Bus\PendingChain;
use WPWhales\Support\Testing\Fakes\BusFake;

/**
 * @method static mixed dispatch(mixed $command)
 * @method static mixed dispatchSync(mixed $command, mixed $handler = null)
 * @method static mixed dispatchNow(mixed $command, mixed $handler = null)
 * @method static \WPWhales\Bus\Batch|null findBatch(string $batchId)
 * @method static \WPWhales\Bus\PendingBatch batch(\WPWhales\Support\Collection|array|mixed $jobs)
 * @method static \WPWhales\Foundation\Bus\PendingChain chain(\WPWhales\Support\Collection|array $jobs)
 * @method static bool hasCommandHandler(mixed $command)
 * @method static bool|mixed getCommandHandler(mixed $command)
 * @method static mixed dispatchToQueue(mixed $command)
 * @method static void dispatchAfterResponse(mixed $command, mixed $handler = null)
 * @method static \WPWhales\Bus\Dispatcher pipeThrough(array $pipes)
 * @method static \WPWhales\Bus\Dispatcher map(array $map)
 * @method static \WPWhales\Support\Testing\Fakes\BusFake except(array|string $jobsToDispatch)
 * @method static void assertDispatched(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $command, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static void assertDispatchedSync(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedSyncTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedSync(string|\Closure $command, callable|null $callback = null)
 * @method static void assertDispatchedAfterResponse(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedAfterResponseTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedAfterResponse(string|\Closure $command, callable|null $callback = null)
 * @method static void assertChained(array $expectedChain)
 * @method static void assertDispatchedWithoutChain(string|\Closure $command, callable|null $callback = null)
 * @method static \WPWhales\Support\Testing\Fakes\ChainedBatchTruthTest chainedBatch(\Closure $callback)
 * @method static void assertBatched(callable $callback)
 * @method static void assertBatchCount(int $count)
 * @method static void assertNothingBatched()
 * @method static \WPWhales\Support\Collection dispatched(string $command, callable|null $callback = null)
 * @method static \WPWhales\Support\Collection dispatchedSync(string $command, callable|null $callback = null)
 * @method static \WPWhales\Support\Collection dispatchedAfterResponse(string $command, callable|null $callback = null)
 * @method static \WPWhales\Support\Collection batched(callable $callback)
 * @method static bool hasDispatched(string $command)
 * @method static bool hasDispatchedSync(string $command)
 * @method static bool hasDispatchedAfterResponse(string $command)
 * @method static \WPWhales\Bus\Batch dispatchFakeBatch(string $name = '')
 * @method static \WPWhales\Bus\Batch recordPendingBatch(\WPWhales\Bus\PendingBatch $pendingBatch)
 * @method static \WPWhales\Support\Testing\Fakes\BusFake serializeAndRestore(bool $serializeAndRestore = true)
 *
 * @see \WPWhales\Bus\Dispatcher
 * @see \WPWhales\Support\Testing\Fakes\BusFake
 */
class Bus extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param  array|string  $jobsToFake
     * @param  \WPWhales\Bus\BatchRepository|null  $batchRepository
     * @return \WPWhales\Support\Testing\Fakes\BusFake
     */
    public static function fake($jobsToFake = [], BatchRepository $batchRepository = null)
    {
        $actualDispatcher = static::isFake()
                ? static::getFacadeRoot()->dispatcher
                : static::getFacadeRoot();

        return \WPWCore\Support\tap(new BusFake($actualDispatcher, $jobsToFake, $batchRepository), function ($fake) {
            static::swap($fake);
        });
    }

    /**
     * Dispatch the given chain of jobs.
     *
     * @param  array|mixed  $jobs
     * @return \WPWhales\Foundation\Bus\PendingDispatch
     */
    public static function dispatchChain($jobs)
    {
        $jobs = is_array($jobs) ? $jobs : func_get_args();

        return (new PendingChain(array_shift($jobs), $jobs))
                    ->dispatch();
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BusDispatcherContract::class;
    }
}
