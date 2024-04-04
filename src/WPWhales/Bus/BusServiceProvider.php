<?php

namespace WPWhales\Bus;

use Aws\DynamoDb\DynamoDbClient;
use WPWhales\Contracts\Bus\Dispatcher as DispatcherContract;
use WPWhales\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use WPWhales\Contracts\Queue\Factory as QueueFactoryContract;
use WPWhales\Contracts\Support\DeferrableProvider;
use WPWhales\Support\Arr;
use WPWhales\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Dispatcher::class, function ($app) {
            return new Dispatcher($app, function ($connection = null) use ($app) {
                return $app[QueueFactoryContract::class]->connection($connection);
            });
        });

        $this->registerBatchServices();

        $this->app->alias(
            Dispatcher::class, DispatcherContract::class
        );

        $this->app->alias(
            Dispatcher::class, QueueingDispatcherContract::class
        );
    }

    /**
     * Register the batch handling services.
     *
     * @return void
     */
    protected function registerBatchServices()
    {
        $this->app->singleton(BatchRepository::class, function ($app) {
            $driver = $app->config->get('queue.batching.driver', 'database');

            return $driver === 'dynamodb'
                ? $app->make(DynamoBatchRepository::class)
                : $app->make(DatabaseBatchRepository::class);
        });

        $this->app->singleton(DatabaseBatchRepository::class, function ($app) {
            return new DatabaseBatchRepository(
                $app->make(BatchFactory::class),
                $app->make('db')->connection($app->config->get('queue.batching.database')),
                $app->config->get('queue.batching.table', 'job_batches')
            );
        });

        $this->app->singleton(DynamoBatchRepository::class, function ($app) {
            $config = $app->config->get('queue.batching');

            $dynamoConfig = [
                'region' => $config['region'],
                'version' => 'latest',
                'endpoint' => $config['endpoint'] ?? null,
            ];

            if (! empty($config['key']) && ! empty($config['secret'])) {
                $dynamoConfig['credentials'] = Arr::only(
                    $config,
                    ['key', 'secret', 'token']
                );
            }

            return new DynamoBatchRepository(
                $app->make(BatchFactory::class),
                new DynamoDbClient($dynamoConfig),
                $app->config->get('app.name'),
                $app->config->get('queue.batching.table', 'job_batches'),
                ttl: $app->config->get('queue.batching.ttl', null),
                ttlAttribute: $app->config->get('queue.batching.ttl_attribute', 'ttl'),
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Dispatcher::class,
            DispatcherContract::class,
            QueueingDispatcherContract::class,
            BatchRepository::class,
            DatabaseBatchRepository::class,
        ];
    }
}
