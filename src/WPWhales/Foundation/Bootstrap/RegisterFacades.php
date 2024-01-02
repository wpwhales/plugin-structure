<?php

namespace WPWhales\Foundation\Bootstrap;

use WPWhales\Contracts\Foundation\Application;
use WPWhales\Foundation\AliasLoader;
use WPWhales\Foundation\PackageManifest;
use WPWhales\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \WPWhales\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get('app.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}
