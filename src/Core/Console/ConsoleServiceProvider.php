<?php

namespace WPWCore\Console;

use WPWCore\ActionScheduler\Console\JobsTableCommand;
use WPWCore\ActionScheduler\Console\ScheduleRefreshCommand;
use WPWCore\ActionScheduler\Console\ScheduleRemoveCanceledCommand;
use WPWCore\Auth\Console\ClearResetsCommand;
use WPWCore\Cache\Console\CacheTableCommand;
use WPWCore\Cache\Console\ClearCommand as CacheClearCommand;
use WPWCore\Cache\Console\ForgetCommand as CacheForgetCommand;
use WPWCore\Console\Application as Artisan;
use WPWCore\Console\Scheduling\ScheduleFinishCommand;
use WPWCore\Console\Scheduling\ScheduleRunCommand;
use WPWCore\Console\Scheduling\ScheduleWorkCommand;
use WPWCore\Database\Console\DumpCommand;
use WPWCore\Database\Console\Migrations\FreshCommand as MigrateFreshCommand;
use WPWCore\Database\Console\Migrations\InstallCommand as MigrateInstallCommand;
use WPWCore\Database\Console\Migrations\MigrateCommand;
use WPWCore\Database\Console\Migrations\MigrateMakeCommand;
use WPWCore\Database\Console\Migrations\RefreshCommand as MigrateRefreshCommand;
use WPWCore\Database\Console\Migrations\ResetCommand as MigrateResetCommand;
use WPWCore\Database\Console\Migrations\RollbackCommand as MigrateRollbackCommand;
use WPWCore\Database\Console\Migrations\StatusCommand as MigrateStatusCommand;
use WPWCore\Database\Console\Seeds\SeedCommand;
use WPWCore\Database\Console\Seeds\SeederMakeCommand;
use WPWCore\Database\Console\WipeCommand;
use WPWCore\Session\Console\SessionTableCommand;
use WPWCore\View\Console\ViewCacheCommand;
use WPWCore\View\Console\ViewClearCommand;
use WPWCore\View\Console\ViewSecureCommand;
use WPWhales\Queue\Console\BatchesTableCommand;
use WPWhales\Queue\Console\ClearCommand as ClearQueueCommand;
use WPWhales\Queue\Console\FailedTableCommand;
use WPWhales\Queue\Console\FlushFailedCommand as FlushFailedQueueCommand;
use WPWhales\Queue\Console\ForgetFailedCommand as ForgetFailedQueueCommand;
use WPWhales\Queue\Console\ListenCommand as QueueListenCommand;
use WPWhales\Queue\Console\ListFailedCommand as ListFailedQueueCommand;
use WPWhales\Queue\Console\RestartCommand as QueueRestartCommand;
use WPWhales\Queue\Console\RetryCommand as QueueRetryCommand;
use WPWhales\Queue\Console\TableCommand;
use WPWhales\Queue\Console\WorkCommand as QueueWorkCommand;
use WPWhales\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'ViewClear'  => 'command.view.clear',
        'ViewCache' => 'command.view.cache',
        'ViewSecure' => 'command.view.secure',
        'Seed'        => 'command.seed',
        'ScheduleRefresh'=>'command.schedule.refresh',
        'ScheduleRemoveCanceled'=>'command.schedule.remove-canceled'
    ];

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $devCommands = [
//        'CacheTable'   => 'command.cache.table',
        'SeederMake'   => 'command.seeder.make',
        'SessionTable' => 'command.session.table',
        'JobsTable' => 'command.jobs.table'
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands(array_merge(
            $this->commands, $this->devCommands
        ));
    }

    /**
     * Register the package's custom Artisan commands.
     *
     * @param array|mixed $commands
     * @return void
     */
    public function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }

    /**
     * Register the given commands.
     *
     * @param array $commands
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            $this->{"register{$command}Command"}();
        }

        $this->commands(array_values($commands));
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.seed', function ($app) {
            return new SeedCommand($app['db']);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerScheduleRefreshCommand()
    {
        $this->app->singleton('command.schedule.refresh', function ($app) {
            return new ScheduleRefreshCommand();
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerScheduleRemoveCanceledCommand()
    {
        $this->app->singleton('command.schedule.remove-canceled', function ($app) {
            return new ScheduleRemoveCanceledCommand();
        });
    }





    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerSessionTableCommand()
    {
        $this->app->singleton('command.session.table', function ($app) {
            return new SessionTableCommand($app['files'],$app["composer"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerJobsTableCommand()
    {
        $this->app->singleton('command.jobs.table', function ($app) {
            return new JobsTableCommand($app['files'],$app["composer"]);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton('command.view.clear', function ($app) {
            return new ViewClearCommand( $app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewSecureCommand()
    {
        $this->app->singleton('command.view.secure', function ($app) {
            return new ViewSecureCommand();
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerViewCacheCommand()
    {
        $this->app->singleton('command.view.cache', function ($app) {
            return new ViewCacheCommand($app['cache']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerCacheTableCommand()
    {
        $this->app->singleton('command.cache.table', function ($app) {
            return new CacheTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerClearResetsCommand()
    {
        $this->app->singleton('command.auth.resets.clear', function () {
            return new ClearResetsCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.migrate', function ($app) {
            return new MigrateCommand($app['migrator'], $app['events']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateInstallCommand()
    {
        $this->app->singleton('command.migrate.install', function ($app) {
            return new MigrateInstallCommand($app['migration.repository']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateMakeCommand()
    {
        $this->app->singleton('command.migrate.make', function ($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.creator'];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateFreshCommand()
    {
        $this->app->singleton('command.migrate.fresh', function () {
            return new MigrateFreshCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.migrate.refresh', function () {
            return new MigrateRefreshCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.migrate.reset', function ($app) {
            return new MigrateResetCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.migrate.rollback', function ($app) {
            return new MigrateRollbackCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMigrateStatusCommand()
    {
        $this->app->singleton('command.migrate.status', function ($app) {
            return new MigrateStatusCommand($app['migrator']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueClearCommand()
    {
        $this->app->singleton('command.queue.clear', function () {
            return new ClearQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueFailedCommand()
    {
        $this->app->singleton('command.queue.failed', function () {
            return new ListFailedQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueForgetCommand()
    {
        $this->app->singleton('command.queue.forget', function () {
            return new ForgetFailedQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueFlushCommand()
    {
        $this->app->singleton('command.queue.flush', function () {
            return new FlushFailedQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueListenCommand()
    {
        $this->app->singleton('command.queue.listen', function ($app) {
            return new QueueListenCommand($app['queue.listener']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueRestartCommand()
    {
        $this->app->singleton('command.queue.restart', function ($app) {
            return new QueueRestartCommand($app['cache.store']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueRetryCommand()
    {
        $this->app->singleton('command.queue.retry', function () {
            return new QueueRetryCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueWorkCommand()
    {
        $this->app->singleton('command.queue.work', function ($app) {
            return new QueueWorkCommand($app['queue.worker'], $app['cache.store']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueFailedTableCommand()
    {
        $this->app->singleton('command.queue.failed-table', function ($app) {
            return new FailedTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueBatchesTableCommand()
    {
        $this->app->singleton('command.queue.batches-table', function ($app) {
            return new BatchesTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerQueueTableCommand()
    {
        $this->app->singleton('command.queue.table', function ($app) {
            return new TableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerSeederMakeCommand()
    {
        $this->app->singleton('command.seeder.make', function ($app) {
            return new SeederMakeCommand($app['files'], $app['composer']);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerWipeCommand()
    {
        $this->app->singleton('command.wipe', function ($app) {
            return new WipeCommand($app['db']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerScheduleFinishCommand()
    {
        $this->app->singleton('command.schedule.finish', function () {
            return new ScheduleFinishCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerScheduleRunCommand()
    {
        $this->app->singleton('command.schedule.run', function () {
            return new ScheduleRunCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerScheduleWorkCommand()
    {
        $this->app->singleton('command.schedule.work', function () {
            return new ScheduleWorkCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerSchemaDumpCommand()
    {
        $this->app->singleton('command.schema.dump', function () {
            return new DumpCommand;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(array_values($this->commands), array_values($this->devCommands));
    }
}
