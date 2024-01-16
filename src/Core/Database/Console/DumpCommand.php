<?php

namespace WPWCore\Database\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Console\Command;
use WPWhales\Contracts\Events\Dispatcher;
use WPWCore\Database\Connection;
use WPWCore\Database\ConnectionResolverInterface;
use WPWCore\Database\Events\SchemaDumped;
use WPWhales\Support\Facades\Config;

#[AsCommand(name: 'schema:dump')]
class DumpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schema:dump
                {--database= : The database connection to use}
                {--path= : The path where the schema dump file should be stored}
                {--prune : Delete all existing migration files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the given database schema';

    /**
     * Execute the console command.
     *
     * @param  \WPWCore\Database\ConnectionResolverInterface  $connections
     * @param  \WPWhales\Contracts\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function handle(ConnectionResolverInterface $connections, Dispatcher $dispatcher)
    {
        $connection = $connections->connection($database = $this->input->getOption('database'));

        $this->schemaState($connection)->dump(
            $connection, $path = $this->path($connection)
        );

        $dispatcher->dispatch(new SchemaDumped($connection, $path));

        $info = 'Database schema dumped';

        if ($this->option('prune')) {
            (new Filesystem)->deleteDirectory(
                \WPWCore\database_path('migrations')
                , $preserve = false
            );

            $info .= ' and pruned';
        }

        $this->components->info($info.' successfully.');
    }

    /**
     * Create a schema state instance for the given connection.
     *
     * @param  \WPWCore\Database\Connection  $connection
     * @return mixed
     */
    protected function schemaState(Connection $connection)
    {
        return $connection->getSchemaState()
                ->withMigrationTable($connection->getTablePrefix().Config::get('database.migrations', 'migrations'))
                ->handleOutputUsing(function ($type, $buffer) {
                    $this->output->write($buffer);
                });
    }

    /**
     * Get the path that the dump should be written to.
     *
     * @param  \WPWCore\Database\Connection  $connection
     */
    protected function path(Connection $connection)
    {
        return \WPWCore\Support\tap($this->option('path') ?: \WPWCore\database_path('schema/' . $connection->getName() . '-schema.sql')
, function ($path) {
            (new Filesystem)->ensureDirectoryExists(dirname($path));
        });
    }
}
