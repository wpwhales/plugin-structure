<?php

namespace WPWhales\Support\Facades;

use Closure;
use WPWhales\Process\Factory;

/**
 * @method static \WPWhales\Process\PendingProcess command(array|string $command)
 * @method static \WPWhales\Process\PendingProcess path(string $path)
 * @method static \WPWhales\Process\PendingProcess timeout(int $timeout)
 * @method static \WPWhales\Process\PendingProcess idleTimeout(int $timeout)
 * @method static \WPWhales\Process\PendingProcess forever()
 * @method static \WPWhales\Process\PendingProcess env(array $environment)
 * @method static \WPWhales\Process\PendingProcess input(\Traversable|resource|string|int|float|bool|null $input)
 * @method static \WPWhales\Process\PendingProcess quietly()
 * @method static \WPWhales\Process\PendingProcess tty(bool $tty = true)
 * @method static \WPWhales\Process\PendingProcess options(array $options)
 * @method static \WPWhales\Contracts\Process\ProcessResult run(array|string|null $command = null, callable|null $output = null)
 * @method static \WPWhales\Process\InvokedProcess start(array|string|null $command = null, callable $output = null)
 * @method static \WPWhales\Process\PendingProcess withFakeHandlers(array $fakeHandlers)
 * @method static \WPWhales\Process\PendingProcess|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \WPWhales\Process\PendingProcess|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \WPWhales\Process\FakeProcessResult result(array|string $output = '', array|string $errorOutput = '', int $exitCode = 0)
 * @method static \WPWhales\Process\FakeProcessDescription describe()
 * @method static \WPWhales\Process\FakeProcessSequence sequence(array $processes = [])
 * @method static bool isRecording()
 * @method static \WPWhales\Process\Factory recordIfRecording(\WPWhales\Process\PendingProcess $process, \WPWhales\Contracts\Process\ProcessResult $result)
 * @method static \WPWhales\Process\Factory record(\WPWhales\Process\PendingProcess $process, \WPWhales\Contracts\Process\ProcessResult $result)
 * @method static \WPWhales\Process\Factory preventStrayProcesses(bool $prevent = true)
 * @method static bool preventingStrayProcesses()
 * @method static \WPWhales\Process\Factory assertRan(\Closure|string $callback)
 * @method static \WPWhales\Process\Factory assertRanTimes(\Closure|string $callback, int $times = 1)
 * @method static \WPWhales\Process\Factory assertNotRan(\Closure|string $callback)
 * @method static \WPWhales\Process\Factory assertDidntRun(\Closure|string $callback)
 * @method static \WPWhales\Process\Factory assertNothingRan()
 * @method static \WPWhales\Process\Pool pool(callable $callback)
 * @method static \WPWhales\Contracts\Process\ProcessResult pipe(callable|array $callback, callable|null $output = null)
 * @method static \WPWhales\Process\ProcessPoolResults concurrently(callable $callback, callable|null $output = null)
 * @method static \WPWhales\Process\PendingProcess newPendingProcess()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 *
 * @see \WPWhales\Process\PendingProcess
 * @see \WPWhales\Process\Factory
 */
class Process extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    /**
     * Indicate that the process factory should fake processes.
     *
     * @param  \Closure|array|null  $callback
     * @return \WPWhales\Process\Factory
     */
    public static function fake(Closure|array $callback = null)
    {
        return \WPWCore\Support\tap(static::getFacadeRoot(), function ($fake) use ($callback) {
            static::swap($fake->fake($callback));
        });
    }
}
