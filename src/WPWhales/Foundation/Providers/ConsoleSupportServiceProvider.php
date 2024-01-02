<?php

namespace WPWhales\Foundation\Providers;

use WPWhales\Contracts\Support\DeferrableProvider;
use WPWhales\Database\MigrationServiceProvider;
use WPWhales\Support\AggregateServiceProvider;

class ConsoleSupportServiceProvider extends AggregateServiceProvider implements DeferrableProvider
{
    /**
     * The provider class names.
     *
     * @var string[]
     */
    protected $providers = [
        ArtisanServiceProvider::class,
        MigrationServiceProvider::class,
        ComposerServiceProvider::class,
    ];
}
