<?php

namespace WPWCore\Queue\Console;

use WPWCore\Console\Command;
use WPWhales\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'queue:failed')]
class ListFailedCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'queue:failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all of the failed queue jobs';

    /**
     * The table headers for the command.
     *
     * @var string[]
     */
    protected $headers = ['ID', 'Class', 'Failed At','Exception'];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (count($jobs = $this->getFailedJobs()) === 0) {
            return $this->components->info('No failed jobs found.');
        }

        $this->newLine();
        $this->displayFailedJobs($jobs);
        $this->newLine();
    }

    /**
     * Compile the failed jobs into a displayable format.
     *
     * @return array
     */
    protected function getFailedJobs()
    {
        $failed = $this->laravel['queue.failer']->all();

        return \WPWCore\Collections\collect($failed)->map(function ($failed) {
            return $this->parseFailedJob((array) $failed);
        })->filter()->all();
    }

    /**
     * Parse the failed job row.
     *
     * @param  array  $failed
     * @return array
     */
    protected function parseFailedJob(array $failed)
    {
        $row = array_values(Arr::except($failed, ["payload","queue","connection"]));



        array_splice($row, 3, 0, $this->extractJobName($failed['payload']) ?: '');

        return $row;
    }

    /**
     * Extract the failed job name from payload.
     *
     * @param  string  $payload
     * @return string|null
     */
    private function extractJobName($payload)
    {
        $payload = json_decode($payload, true);

        if ($payload && (! isset($payload['data']['command']))) {
            return $payload['job'] ?? null;
        } elseif ($payload && isset($payload['data']['command'])) {
            return $this->matchJobName($payload);
        }
    }

    /**
     * Match the job name from the payload.
     *
     * @param  array  $payload
     * @return string|null
     */
    protected function matchJobName($payload)
    {
        preg_match('/"([^"]+)"/', $payload['data']['command'], $matches);

        return $matches[1] ?? $payload['job'] ?? null;
    }

    /**
     * Display the failed jobs in the console.
     *
     * @param  array  $jobs
     * @return void
     */
    protected function displayFailedJobs(array $jobs)
    {

        $jobs = \WPWCore\Collections\collect($jobs)->map(function($job){

            $exceptionMessage =$this->parseExceptionMessage($job[1]);
            $job[1]=$job[3];
            $job[3] = $exceptionMessage;
            return $job;

        });

        $this->table($this->headers,$jobs);

    }

    private function parseExceptionMessage($exception)
    {


        $messageStartPos = strpos($exception, 'Exception:');
        $messageEndPos = strpos($exception, 'Stack trace:');
        if ($messageStartPos !== false && $messageEndPos !== false) {
            $message = substr($exception, $messageStartPos, $messageEndPos - $messageStartPos);
            return trim($message);
        }
        return 'N/A';
    }
}
