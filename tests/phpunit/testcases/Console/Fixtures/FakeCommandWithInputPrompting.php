<?php

namespace Tests\Console\Fixtures;

use BadMethodCallException;
use WPWCore\Console\Command;
use WPWhales\Contracts\Console\PromptsForMissingInput;

class FakeCommandWithInputPrompting extends Command implements PromptsForMissingInput
{
    use \WPWCore\Console\Concerns\PromptsForMissingInput {
        askPersistently as traitAskPersistently;
    }

    protected $signature = 'fake-command-for-testing {name : An argument}';

    public function __construct(private $expectToRequestInput = true)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return self::SUCCESS;
    }

    private function askPersistently($question)
    {
        if (! $this->expectToRequestInput) {
            throw new BadMethodCallException('No prompts for input were expected, but a question was asked.');
        }

        return $this->traitAskPersistently($question);
    }
}
