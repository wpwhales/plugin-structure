<?php

namespace WPWhales\Auth;

use WPWhales\Auth\Access\Gate;
use WPWhales\Auth\Middleware\RequirePassword;
use WPWhales\Contracts\Auth\Access\Gate as GateContract;
use WPWhales\Contracts\Auth\Authenticatable as AuthenticatableContract;
use WPWhales\Contracts\Routing\ResponseFactory;
use WPWhales\Contracts\Routing\UrlGenerator;
use WPWhales\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAuthenticator();
        $this->registerUserResolver();
        $this->registerAccessGate();
        $this->registerRequirePassword();
        $this->registerRequestRebindHandler();
        $this->registerEventRebindHandler();
    }

    /**
     * Register the authenticator services.
     *
     * @return void
     */
    protected function registerAuthenticator()
    {
        $this->app->singleton('auth', fn ($app) => new AuthManager($app));

        $this->app->singleton('auth.driver', fn ($app) => $app['auth']->guard());
    }

    /**
     * Register a resolver for the authenticated user.
     *
     * @return void
     */
    protected function registerUserResolver()
    {
        $this->app->bind(AuthenticatableContract::class, fn ($app) => call_user_func($app['auth']->userResolver()));
    }

    /**
     * Register the access gate service.
     *
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(GateContract::class, function ($app) {
            return new Gate($app, fn () => call_user_func($app['auth']->userResolver()));
        });
    }

    /**
     * Register a resolver for the authenticated user.
     *
     * @return void
     */
    protected function registerRequirePassword()
    {
        $this->app->bind(RequirePassword::class, function ($app) {
            return new RequirePassword(
                $app[ResponseFactory::class],
                $app[UrlGenerator::class],
                $app['config']->get('auth.password_timeout')
            );
        });
    }

    /**
     * Handle the re-binding of the request binding.
     *
     * @return void
     */
    protected function registerRequestRebindHandler()
    {
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function ($guard = null) use ($app) {
                return call_user_func($app['auth']->userResolver(), $guard);
            });
        });
    }

    /**
     * Handle the re-binding of the event dispatcher binding.
     *
     * @return void
     */
    protected function registerEventRebindHandler()
    {
        $this->app->rebinding('events', function ($app, $dispatcher) {
            if (! $app->resolved('auth') ||
                $app['auth']->hasResolvedGuards() === false) {
                return;
            }

            if (method_exists($guard = $app['auth']->guard(), 'setDispatcher')) {
                $guard->setDispatcher($dispatcher);
            }
        });
    }
}
