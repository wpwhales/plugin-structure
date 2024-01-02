<?php

namespace WPWhales\Console;

use Carbon\CarbonInterval;
use WPWhales\Cache\DynamoDbStore;
use WPWhales\Contracts\Cache\Factory as Cache;
use WPWhales\Contracts\Cache\LockProvider;
use WPWhales\Support\InteractsWithTime;

class CacheCommandMutex implements CommandMutex
{
    use InteractsWithTime;

    /**
     * The cache factory implementation.
     *
     * @var \WPWhales\Contracts\Cache\Factory
     */
    public $cache;

    /**
     * The cache store that should be used.
     *
     * @var string|null
     */
    public $store = null;

    /**
     * Create a new command mutex.
     *
     * @param  \WPWhales\Contracts\Cache\Factory  $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Attempt to obtain a command mutex for the given command.
     *
     * @param  \WPWhales\Console\Command  $command
     * @return bool
     */
    public function create($command)
    {
        $store = $this->cache->store($this->store);

        $expiresAt = method_exists($command, 'isolationLockExpiresAt')
            ? $command->isolationLockExpiresAt()
            : CarbonInterval::hour();

        if ($this->shouldUseLocks($store->getStore())) {
            return $store->getStore()->lock(
                $this->commandMutexName($command),
                $this->secondsUntil($expiresAt)
            )->get();
        }

        return $store->add($this->commandMutexName($command), true, $expiresAt);
    }

    /**
     * Determine if a command mutex exists for the given command.
     *
     * @param  \WPWhales\Console\Command  $command
     * @return bool
     */
    public function exists($command)
    {
        $store = $this->cache->store($this->store);

        if ($this->shouldUseLocks($store->getStore())) {
            $lock = $store->getStore()->lock($this->commandMutexName($command));

            return tap(! $lock->get(), function ($exists) use ($lock) {
                if ($exists) {
                    $lock->release();
                }
            });
        }

        return $this->cache->store($this->store)->has($this->commandMutexName($command));
    }

    /**
     * Release the mutex for the given command.
     *
     * @param  \WPWhales\Console\Command  $command
     * @return bool
     */
    public function forget($command)
    {
        $store = $this->cache->store($this->store);

        if ($this->shouldUseLocks($store->getStore())) {
            return $store->getStore()->lock($this->commandMutexName($command))->forceRelease();
        }

        return $this->cache->store($this->store)->forget($this->commandMutexName($command));
    }

    /**
     * Get the isolatable command mutex name.
     *
     * @param  \WPWhales\Console\Command  $command
     * @return string
     */
    protected function commandMutexName($command)
    {
        $baseName = 'framework'.DIRECTORY_SEPARATOR.'command-'.$command->getName();

        return method_exists($command, 'isolatableId')
            ? $baseName.'-'.$command->isolatableId()
            : $baseName;
    }

    /**
     * Specify the cache store that should be used.
     *
     * @param  string|null  $store
     * @return $this
     */
    public function useStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Determine if the given store should use locks for command mutexes.
     *
     * @param  \WPWhales\Contracts\Cache\Store  $store
     * @return bool
     */
    protected function shouldUseLocks($store)
    {
        return $store instanceof LockProvider && ! $store instanceof DynamoDbStore;
    }
}
