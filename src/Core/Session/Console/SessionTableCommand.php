<?php

namespace WPWCore\Session\Console;

use WPWCore\Console\MigrationGeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'session:table')]
class SessionTableCommand extends MigrationGeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'session:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the session database table';

    /**
     * Get the migration table name.
     *
     * @return string
     */
    protected function migrationTableName()
    {
        return \WPWCore\config("session.table");
    }

    /**
     * Get the path to the migration stub file.
     *
     * @return string
     */
    protected function migrationStubFile()
    {
        return __DIR__.'/stubs/database.stub';
    }
}
