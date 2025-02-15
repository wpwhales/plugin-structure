<?php

namespace WPWCore\Testing\Concerns;

use WPWCore\Console\OutputStyle;
use WPWhales\Contracts\Console\Kernel;
use WPWhales\Testing\PendingCommand;

trait InteractsWithConsole
{
    /**
     * Indicates if the console output should be mocked.
     *
     * @var bool
     */
    public $mockConsoleOutput = true;

    /**
     * All of the expected output lines.
     *
     * @var array
     */
    public $expectedOutput = [];

    /**
     * All of the expected text to be present in the output.
     *
     * @var array
     */
    public $expectedOutputSubstrings = [];

    /**
     * All of the output lines that aren't expected to be displayed.
     *
     * @var array
     */
    public $unexpectedOutput = [];

    /**
     * All of the text that is not expected to be present in the output.
     *
     * @var array
     */
    public $unexpectedOutputSubstrings = [];

    /**
     * All of the expected output tables.
     *
     * @var array
     */
    public $expectedTables = [];

    /**
     * All of the expected questions.
     *
     * @var array
     */
    public $expectedQuestions = [];

    /**
     * All of the expected choice questions.
     *
     * @var array
     */
    public $expectedChoices = [];

    /**
     * Call artisan command and return code.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \WPWhales\Testing\PendingCommand|int
     */
    public function artisan($command, $parameters = [])
    {
        $this->app->singletonIf(Kernel::class,\WPWCore\Console\Kernel::class);

        if (! $this->mockConsoleOutput) {
            return $this->app[Kernel::class]->call($command, $parameters);
        }

        return new PendingCommand($this, $this->app, $command, $parameters);
    }

    /**
     * Disable mocking the console output.
     *
     * @return $this
     */
    protected function withoutMockingConsoleOutput()
    {
        $this->mockConsoleOutput = false;

        $this->app->offsetUnset(OutputStyle::class);

        return $this;
    }
}
