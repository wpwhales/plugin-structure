<?php

namespace WPWCore\Database\Console;

use WPWhales\Contracts\Events\Dispatcher;
use WPWCore\Database\ConnectionResolverInterface;
use WPWCore\Database\Events\DatabaseBusy;
use WPWhales\Support\Composer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db:monitor')]
class MonitorCommand extends DatabaseInspectionCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor
                {--databases= : The database connections to monitor}
                {--max= : The maximum number of connections that can be open before an event is dispatched}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the number of connections on the specified database';

    /**
     * The connection resolver instance.
     *
     * @var \WPWCore\Database\ConnectionResolverInterface
     */
    protected $connection;

    /**
     * The events dispatcher instance.
     *
     * @var \WPWhales\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new command instance.
     *
     * @param  \WPWCore\Database\ConnectionResolverInterface  $connection
     * @param  \WPWhales\Contracts\Events\Dispatcher  $events
     * @param  \WPWhales\Support\Composer  $composer
     */
    public function __construct(ConnectionResolverInterface $connection, Dispatcher $events, Composer $composer)
    {
        parent::__construct($composer);

        $this->connection = $connection;
        $this->events = $events;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $databases = $this->parseDatabases($this->option('databases'));

        $this->displayConnections($databases);

        if ($this->option('max')) {
            $this->dispatchEvents($databases);
        }
    }

    /**
     * Parse the database into an array of the connections.
     *
     * @param  string  $databases
     * @return \WPWhales\Support\Collection
     */
    protected function parseDatabases($databases)
    {
        return \WPWCore\Collections\collect(explode(',', $databases))
            ->map(function ($database) {
                if (!$database) {
                    $database = $this->laravel['config']['database.default'];
                }

                $maxConnections = $this->option('max');

                return [
                    'database'    => $database,
                    'connections' => $connections = $this->getConnectionCount($this->connection->connection($database)),
                    'status'      => $maxConnections && $connections >= $maxConnections ? '<fg=yellow;options=bold>ALERT</>' : '<fg=green;options=bold>OK</>',
                ];
            });
    }

    /**
     * Display the databases and their connection counts in the console.
     *
     * @param  \WPWhales\Support\Collection  $databases
     * @return void
     */
    protected function displayConnections($databases)
    {
        $this->newLine();

        $this->components->twoColumnDetail('<fg=gray>Database name</>', '<fg=gray>Connections</>');

        $databases->each(function ($database) {
            $status = '['.$database['connections'].'] '.$database['status'];

            $this->components->twoColumnDetail($database['database'], $status);
        });

        $this->newLine();
    }

    /**
     * Dispatch the database monitoring events.
     *
     * @param  \WPWhales\Support\Collection  $databases
     * @return void
     */
    protected function dispatchEvents($databases)
    {
        $databases->each(function ($database) {
            if ($database['status'] === '<fg=green;options=bold>OK</>') {
                return;
            }

            $this->events->dispatch(
                new DatabaseBusy(
                    $database['database'],
                    $database['connections']
                )
            );
        });
    }
}
