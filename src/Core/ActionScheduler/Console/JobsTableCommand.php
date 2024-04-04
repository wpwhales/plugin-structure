<?php

namespace WPWCore\ActionScheduler\Console;

use WPWCore\Console\MigrationGeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'schedule:jobs-table')]
class JobsTableCommand extends MigrationGeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'schedule:jobs-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the jobs database table';

    /**
     * Get the migration table name.
     *
     * @return string
     */
    protected function migrationTableName()
    {
        return "wpwcore_jobs";
    }

    /**
     * Get the path to the migration stub file.
     *
     * @return string
     */
    protected function migrationStubFile()
    {
        return __DIR__.'/stubs/jobs.stub';
    }
}
