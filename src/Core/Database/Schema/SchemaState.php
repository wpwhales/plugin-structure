<?php

namespace WPWCore\Database\Schema;

use Symfony\Component\Process\Process;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Database\Connection;

abstract class SchemaState
{
    /**
     * The connection instance.
     *
     * @var \WPWCore\Database\Connection
     */
    protected $connection;

    /**
     * The filesystem instance.
     *
     * @var \WPWCore\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The name of the application's migration table.
     *
     * @var string
     */
    protected $migrationTable = 'migrations';

    /**
     * The process factory callback.
     *
     * @var callable
     */
    protected $processFactory;

    /**
     * The output callable instance.
     *
     * @var callable
     */
    protected $output;

    /**
     * Create a new dumper instance.
     *
     * @param  \WPWCore\Database\Connection  $connection
     * @param  \WPWCore\Filesystem\Filesystem|null  $files
     * @param  callable|null  $processFactory
     * @return void
     */
    public function __construct(Connection $connection, Filesystem $files = null, callable $processFactory = null)
    {
        $this->connection = $connection;

        $this->files = $files ?: new Filesystem;

        $this->processFactory = $processFactory ?: function (...$arguments) {
            return Process::fromShellCommandline(...$arguments)->setTimeout(null);
        };

        $this->handleOutputUsing(function () {
            //
        });
    }

    /**
     * Dump the database's schema into a file.
     *
     * @param  \WPWCore\Database\Connection  $connection
     * @param  string  $path
     * @return void
     */
    abstract public function dump(Connection $connection, $path);

    /**
     * Load the given schema file into the database.
     *
     * @param  string  $path
     * @return void
     */
    abstract public function load($path);

    /**
     * Create a new process instance.
     *
     * @param  mixed  ...$arguments
     * @return \Symfony\Component\Process\Process
     */
    public function makeProcess(...$arguments)
    {
        return call_user_func($this->processFactory, ...$arguments);
    }

    /**
     * Specify the name of the application's migration table.
     *
     * @param  string  $table
     * @return $this
     */
    public function withMigrationTable(string $table)
    {
        $this->migrationTable = $table;

        return $this;
    }

    /**
     * Specify the callback that should be used to handle process output.
     *
     * @param  callable  $output
     * @return $this
     */
    public function handleOutputUsing(callable $output)
    {
        $this->output = $output;

        return $this;
    }
}
