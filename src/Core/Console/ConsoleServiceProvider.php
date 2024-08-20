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
use WPWCore\Console\Commands\ComponentMakeCommand;
use WPWCore\Console\Commands\ConsoleMakeCommand;
use WPWCore\Console\Commands\ControllerMakeCommand;
use WPWCore\Console\Commands\EventListCommand;
use WPWCore\Console\Commands\EventMakeCommand;
use WPWCore\Console\Commands\ExceptionMakeCommand;
use WPWCore\Console\Commands\FactoryGenerateCommand;
use WPWCore\Console\Commands\FactoryMakeCommand;
use WPWCore\Console\Commands\HookMakeCommand;
use WPWCore\Console\Commands\ListenerMakeCommand;
use WPWCore\Console\Commands\MiddlewareMakeCommand;
use WPWCore\Console\Commands\ModelMakeCommand;
use WPWCore\Console\Commands\ObserverMakeCommand;
use WPWCore\Console\Commands\PolicyMakeCommand;
use WPWCore\Console\Commands\ProviderMakeCommand;
use WPWCore\Console\Commands\RuleMakeCommand;
use WPWCore\Console\Commands\ShortcodeMakeCommand;
use WPWCore\Console\Commands\TestMakeCommand;
use WPWCore\Console\Commands\WidgetMakeCommand;
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
use WPWCore\Queue\Console\ClearCommand as ClearQueueCommand;
use WPWCore\Queue\Console\FailedTableCommand;
use WPWCore\Queue\Console\FlushFailedCommand as FlushFailedQueueCommand;
use WPWCore\Queue\Console\ForgetFailedCommand as ForgetFailedQueueCommand;
use WPWCore\Queue\Console\ListenCommand as QueueListenCommand;
use WPWCore\Queue\Console\ListFailedCommand as ListFailedQueueCommand;
use WPWCore\Queue\Console\RestartCommand as QueueRestartCommand;
use WPWCore\Queue\Console\RetryCommand as QueueRetryCommand;
use WPWCore\Queue\Console\TableCommand;
use WPWCore\Queue\Console\WorkCommand as QueueWorkCommand;
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
        'ScheduleRemoveCanceled'=>'command.schedule.remove-canceled',

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
        'JobsTable' => 'command.jobs.table',
        'FailedJobsTable' => 'command.failed-jobs.table',
        'ForgetFailed'=>'command.forget.failed',
        'FlushFailed'=>'command.flush.failed',
        'ListFailed'=>'command.list.failed',
        'RetryFailed'=>'command.retry.failed',
        'ComponentMake' => 'command.component.make',
        'ConsoleMake' => 'command.console.make',
        'ControllerMake' => 'command.controller.make',
        'EventList' => 'command.event.list',
        'EventMake' => 'command.event.make',
        'ListenerMake' => 'command.listener.make',
        'ExceptionMake' => 'command.exception.make',
        'FactoryMake' => 'command.factory.make',
        'FactoryGenerate'=>'command.factory.generate',
        'ShortcodeMake' => 'command.shortcode.make',
        'HookMake' => 'command.hook.make',
        'WidgetMake' => 'command.widget.make',
        'MiddlewareMake' => 'command.middleware.make',
        'ModelMake' => 'command.model.make',
        'ObserverMake' => 'command.observer.make',
        'ProviderMake' => 'command.provider.make',
        'RuleMake' => 'command.rule.make',
        'TestMake' => 'command.test.make',

    ];

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerObserverMakeCommand()
    {
        $this->app->singleton('command.observer.make', function ($app) {
            return new ObserverMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerPolicyMakeCommand()
    {
        $this->app->singleton('command.policy.make', function ($app) {
            return new PolicyMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerTestMakeCommand()
    {
        $this->app->singleton('command.test.make', function ($app) {
            return new TestMakeCommand($app["files"]);
        });
    }
    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerRuleMakeCommand()
    {
        $this->app->singleton('command.rule.make', function ($app) {
            return new RuleMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerProviderMakeCommand()
    {
        $this->app->singleton('command.provider.make', function ($app) {
            return new ProviderMakeCommand($app["files"]);
        });
    }



    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerModelMakeCommand()
    {
        $this->app->singleton('command.model.make', function ($app) {
            return new ModelMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMiddlewareMakeCommand()
    {
        $this->app->singleton('command.middleware.make', function ($app) {
            return new MiddlewareMakeCommand($app["files"]);
        });
    }

    protected function registerWidgetMakeCommand()
    {

        $this->app->singleton('command.widget.make', function ($app) {
            return new WidgetMakeCommand($app["files"],$app["config"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerShortcodeMakeCommand()
    {

        $this->app->singleton('command.shortcode.make', function ($app) {
            return new ShortcodeMakeCommand($app["files"],$app["config"]);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerHookMakeCommand()
    {

        $this->app->singleton('command.hook.make', function ($app) {
            return new HookMakeCommand($app["files"],$app["config"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFactoryGenerateCommand()
    {

        $this->app->singleton('command.factory.generate', function ($app) {
            return new FactoryGenerateCommand($app["files"],$app['view']);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFactoryMakeCommand()
    {
        $this->app->singleton('command.factory.make', function ($app) {
            return new FactoryMakeCommand($app["files"]);
        });
    }


    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerExceptionMakeCommand()
    {
        $this->app->singleton('command.exception.make', function ($app) {
            return new ExceptionMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerListenerMakeCommand()
    {
        $this->app->singleton('command.listener.make', function ($app) {
            return new ListenerMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerEventMakeCommand()
    {
        $this->app->singleton('command.event.make', function ($app) {
            return new EventMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerEventListCommand()
    {
        $this->app->singleton('command.event.list', function ($app) {
            return new EventListCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerControllerMakeCommand()
    {
        $this->app->singleton('command.controller.make', function ($app) {
            return new ControllerMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConsoleMakeCommand()
    {
        $this->app->singleton('command.console.make', function ($app) {
            return new ConsoleMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerComponentMakeCommand()
    {
        $this->app->singleton('command.component.make', function ($app) {
            return new ComponentMakeCommand($app["files"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerMakeCommandsCommand()
    {
        $this->app->singleton('command.', function ($app) {
            return new SeedCommand($app['db']);
        });
    }

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
    protected function registerListFailedCommand()
    {
        $this->app->singleton('command.list.failed', function () {
            return new ListFailedQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerForgetFailedCommand()
    {
        $this->app->singleton('command.forget.failed', function () {
            return new ForgetFailedQueueCommand;
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFlushFailedCommand()
    {
        $this->app->singleton('command.flush.failed', function () {
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
    protected function registerRetryFailedCommand()
    {
        $this->app->singleton('command.retry.failed', function () {
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
            return new TableCommand($app['files'],$app["composer"]);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerFailedJobsTableCommand()
    {
        $this->app->singleton('command.failed-jobs.table', function ($app) {
            return new FailedTableCommand($app['files'],$app["composer"]);
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
