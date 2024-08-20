<?php

namespace WPWCore;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as PsrResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use WPWCore\Auth\AuthManager;
use WPWCore\Auth\AuthServiceProvider;
use WPWCore\Cache\CacheServiceProvider;
use WPWCore\Console\ConsoleServiceProvider;
use WPWCore\Console\Kernel;
use WPWCore\DashboardNotices\AdminNotice;
use WPWCore\DashboardNotices\Notices;
use WPWCore\Encryption\EncryptionServiceProvider;
use WPWCore\Events\EventServiceProvider;
use WPWCore\Filesystem\Filesystem;
use WPWCore\Filesystem\FilesystemServiceProvider;
use WPWCore\Hashing\HashServiceProvider;
use WPWCore\Http\Request;
use WPWCore\Menu\MenuBuilder;
use WPWCore\Pagination\PaginationServiceProvider;
use WPWCore\Routing\BindingResolver;
use WPWCore\Routing\Router;
use WPWCore\Session\StartSession;
use WPWCore\View\ViewServiceProvider;
use WPWhales\Broadcasting\BroadcastServiceProvider;
use WPWhales\Bus\BusServiceProvider;
use WPWhales\Config\Repository as ConfigRepository;
use WPWhales\Container\Container;
use WPWhales\Contracts\Auth\Access\Gate;
use WPWhales\Contracts\Broadcasting\Broadcaster;
use WPWhales\Contracts\Broadcasting\Factory;
use WPWhales\Contracts\Bus\Dispatcher;
use WPWhales\Contracts\Container\BindingResolutionException;
use WPWCore\Database\DatabaseServiceProvider;
use WPWCore\Database\MigrationServiceProvider;
use WPWCore\Log\LogManager;
use WPWhales\Contracts\Filesystem\FileNotFoundException;
use WPWhales\Queue\QueueServiceProvider;
use WPWhales\Support\Composer;
use WPWhales\Support\Facades\Facade;
use WPWhales\Support\Facades\Menu;
use WPWhales\Support\ServiceProvider;
use WPWhales\Support\Str;
use WPWhales\Translation\TranslationServiceProvider;
use WPWhales\Validation\ValidationServiceProvider;

class Application extends Container
{
    use Concerns\RoutesRequests,
        Concerns\RegisterActionScheduler,
        Concerns\RegistersExceptionHandlers;

    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;


    /**
     * This will hold all the registered hooks
     * @var array
     */
    protected $registeredHooks = [];

    /**
     * The domain of the plugin (used for translations).
     *
     * @var string
     */
    protected $pluginDomain;


    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];

    /**
     * The custom storage path defined by the developer.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The Router instance.
     *
     * @var \WPWCore\Routing\Router
     */
    public $router;

    /**
     * The Admin Ajax Router instance.
     *
     * @var \WPWCore\Routing\Router
     */
    public $adminAjaxRouter;

    /**
     * The Wordpress Router instance.
     *
     * @var \WPWCore\Routing\Router
     */
    public $wordpressRouter;

    /**
     * The Admin Ajax Router instance.
     *
     * @var \WPWCore\Routing\Router
     */
    public $webRouter;

    /**
     * The array of terminating callbacks.
     *
     * @var callable[]
     */
    protected $terminatingCallbacks = [];


    /**
     * The array of shutdown callbacks.
     *
     * @var callable[]
     */
    protected $shutdownCallbacks = [];


    /**
     *
     * @var string $adminMenuFilePath
     */
    protected $adminMenuFilePath = "";


    public function getRegisteredHooks( $hookName = ""){

        if(empty($hookName)){

            return $this->registeredHooks;
        }

        return $this->registeredHooks[$hookName];
    }


    public function registerHook($hookInstance){

        $this->registeredHooks[$hookInstance::class] = $hookInstance;

    }

    /**
     * Create a new Lumen application instance.
     *
     * @param string|null $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;

        $this->bootstrapContainer();
        $this->bootstrapRouter();
        $this->loadBaseConfigs();


        $this->sendQueuedCookiesOnTemplateRedirect();


        $this->loadShutDownMethodWithWordpress();


        $this->registerWPCliCommand();

    }

    public function withAdminMenuHandler($path = "")
    {


        $this->adminMenuFilePath = $path;

        $this->singleton("menu", function ($app) {
            return new MenuBuilder($app);
        });

        add_action("admin_menu", [$this, "loadAdminMenus"]);

    }

    public function loadAdminMenus()
    {
        if (!empty($this->adminMenuFilePath) && !file_exists($this->adminMenuFilePath)) {
            throw new FileNotFoundException("Menu file doesn't exists'");
        }

        if (file_exists($this->adminMenuFilePath)) {

            require $this->adminMenuFilePath;
        }


        Menu::register();
    }


    protected function registerWPCliCommand()
    {

        if (class_exists('\WP_CLI')) {

            \WP_CLI::add_command('wpwcore', function ($args, $assoc_args) {


                $artisan = $this->make(\WPWhales\Contracts\Console\Kernel::class);

                $status = $artisan->handle((new \WPWCore\Console\ArgvInput()), new ConsoleOutput());
                // Bind a command
                \WP_CLI::halt($status);

            });
        }
    }


    protected function withDashboardNotices()
    {

        AdminNotice::init();


    }


    public function getPluginDomain()
    {

        return $this->pluginDomain;
    }

    public function setPluginDomain($domain)
    {

        $this->pluginDomain = $domain;

        return $this;
    }

    protected function loadShutDownMethodWithWordpress()
    {
        add_action("shutdown", [$this, "shutdown"]);
    }

    public function initSession()
    {


        $session = $this->make('session');
        $start_session = new StartSession($session);
        $start_session->handle();

        $this->shutting_down(function () {


            $session_name = $this->make('session')->getName();
            $session_id = $this->make('session')->getId();

            //First make sure that we have sent the session cookie or the request has the session cookie
            //then save the session otherwise leave it.
            if (\WPWCore\app("cookie")->isCookieSent($session_name) ||
                (\WPWCore\app("request")->cookies->get($session_name) === $session_id)) {

                $this->make('session')->save();

            }

        });

    }

    protected function sendQueuedCookiesOnTemplateRedirect()
    {

        //Will handle both admin and public routes
        add_action("init", [$this, "sendCookieHeaders"]);

        //Specially if want to send any cookie in shortcodes or hooks
        add_action("template_redirect", [$this, "sendCookieHeaders"]);

    }


    public function sendCookieHeaders()
    {
        $cookie = $this->make("cookie");
        $cookie->sendHeaders();
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerCookieBindings()
    {
        $this->singleton('cookie', function () {
            return $this->loadComponent('session', 'WPWCore\Cookie\CookieServiceProvider', 'cookie');
        });
    }

    protected function registerAssetsBindings()
    {
        $this->singleton('assets', function () {
            return $this->loadComponent('assets', 'WPWCore\Assets\AssetsServiceProvider', 'assets');
        });

        $this->singleton('assets.manifest', function () {
            return $this->loadComponent('assets', 'WPWCore\Assets\AssetsServiceProvider', 'assets.manifest');
        });
    }

    protected function registerSessionBindings()
    {


        $this->singleton('session', function () {
            return $this->loadComponent('session', 'WPWCore\Session\SessionServiceProvider');
        });

        $this->singleton('session.store', function () {
            return $this->loadComponent('session', 'WPWCore\Session\SessionServiceProvider', 'session.store');
        });

    }


    protected function loadBaseConfigs()
    {

        $this->configure("app");
        $this->configure("view");
        $this->configure("hooks");
        $this->configure("shortcodes");
        $this->configure("widgets");

    }

    protected function createRoutesFromFile($path, $attributes = [], $routerInstance = null)
    {
        if (!file_exists($path)) {
            throw new \Exception("Unable to load the route files.please provide the correct path");
        }

        if (!is_null($routerInstance) && is_a($routerInstance, Router::class)) {
            $routerInstance->group($attributes, function ($router) use ($path) {


                require $path;
            });
        } else {
            $this->router->group($attributes, function ($router) use ($path) {


                require $path;
            });
        }


    }

    public function createAjaxRoutesFromFile($path, $attributes = [])
    {
        if (wp_doing_ajax()) {
            $this->createRoutesFromFile($path, $attributes);
        }

        $this->createRoutesFromFile($path, $attributes, $this->adminAjaxRouter);


    }

    public function createWordpressRoutesFromFile($path, $attributes = [])
    {

        $this->createRoutesFromFile($path, $attributes, $this->wordpressRouter);

    }

    public function createWebRoutesFromFile($path, $attributes = [])
    {
        if (!wp_doing_ajax()) {

            $this->createRoutesFromFile($path, $attributes);
        }

        $this->createRoutesFromFile($path, $attributes, $this->webRouter);


    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);


        //TODO Will handle it later after integrating more features
        $this->instance('path', $this->path());

        $this->instance('env', $this->environment());

        $this->registerContainerAliases();
    }

    /**
     * Bootstrap the router instance.
     *
     * @return void
     */
    public function bootstrapRouter()
    {

        $this->app->instance('bindingResolver', new BindingResolver([$this, 'make']));

        $this->router = new Router($this);

        $this->adminAjaxRouter = new Router($this);
        $this->webRouter = new Router($this);
        $this->wordpressRouter = new Router($this);
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'WPWhales Core 10.0';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Get or check the current application environment.
     *
     * @param mixed
     * @return string
     */
    public function environment()
    {

        $env = config('app.env', 'production');

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }

            return false;
        }

        return $env;
    }

    /**
     * Determine if the application is in the local environment.
     *
     * @return bool
     */
    public function isLocal()
    {
        return $this->environment() === 'local';
    }

    /**
     * Determine if the application is in the production environment.
     *
     * @return bool
     */
    public function isProduction()
    {
        return $this->environment() === 'production';
    }

    /**
     * Determine if the given service provider is loaded.
     *
     * @param string $provider
     * @return bool
     */
    public function providerIsLoaded(string $provider)
    {
        return isset($this->loadedProviders[$provider]);
    }

    /**
     * Register a service provider with the application.
     *
     * @param \WPWhales\Support\ServiceProvider|string $provider
     * @return void
     */
    public function register($provider)
    {
        if (!$provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Register a deferred provider and service.
     *
     * @param string $provider
     * @return void
     */
    public function registerDeferredProvider($provider)
    {
        $this->register($provider);
    }

    /**
     * Boots the registered providers.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->loadedProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param \WPWhales\Support\ServiceProvider $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (!$this->bound($abstract) &&
            array_key_exists($abstract, $this->availableBindings) &&
            !array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)) {
            $this->{$method = $this->availableBindings[$abstract]}();

            $this->ranServiceBinders[$method] = true;
        }


        return parent::make($abstract, $parameters);
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerAuthBindings()
    {
        $this->singleton('auth', function () {
            return $this->loadComponent('auth', AuthServiceProvider::class, 'auth');
        });

        $this->singleton('auth.driver', function () {
            return $this->loadComponent('auth', AuthServiceProvider::class, 'auth.driver');
        });

        $this->singleton(AuthManager::class, function () {
            return $this->loadComponent('auth', AuthServiceProvider::class, 'auth');
        });

        $this->singleton(Gate::class, function () {
            return $this->loadComponent('auth', AuthServiceProvider::class, Gate::class);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBroadcastingBindings()
    {
        $this->singleton(Factory::class, function () {
            return $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Factory::class);
        });

        $this->singleton(Broadcaster::class, function () {
            return $this->loadComponent('broadcasting', BroadcastServiceProvider::class, Broadcaster::class);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBusBindings()
    {
        $this->singleton(Dispatcher::class, function () {
            $this->register(BusServiceProvider::class);

            return $this->make(Dispatcher::class);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerCacheBindings()
    {
        $this->singleton('cache', function () {
            return $this->loadComponent('cache', CacheServiceProvider::class);
        });
        $this->singleton('cache.store', function () {
            return $this->loadComponent('cache', CacheServiceProvider::class, 'cache.store');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerComposerBindings()
    {
        $this->singleton('composer', function ($app) {
            return new Composer($app->make('files'), $this->basePath());
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository;
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            $this->configure('app');

            return $this->loadComponent(
                'database', [
                DatabaseServiceProvider::class,
                PaginationServiceProvider::class,
            ], 'db'
            );
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEncrypterBindings()
    {
        $this->singleton('encrypter', function () {
            return $this->loadComponent('app', EncryptionServiceProvider::class, 'encrypter');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEventBindings()
    {
        $this->singleton('events', function () {
            $this->register(EventServiceProvider::class);

            return $this->make('events');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesystemBindings()
    {
        $this->singleton('filesystem', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem');
        });
        $this->singleton('filesystem.disk', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.disk');
        });
        $this->singleton('filesystem.cloud', function () {
            return $this->loadComponent('filesystems', FilesystemServiceProvider::class, 'filesystem.cloud');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerHashBindings()
    {
        $this->singleton('hash', function () {
            $this->register(HashServiceProvider::class);

            return $this->make('hash');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerLogBindings()
    {
        $this->singleton(LoggerInterface::class, function () {
            $this->configure('logging');

            return new LogManager($this);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerQueueBindings()
    {
        $this->singleton('queue', function () {
            return $this->loadComponent('queue', QueueServiceProvider::class, 'queue');
        });
        $this->singleton('queue.connection', function () {
            return $this->loadComponent('queue', QueueServiceProvider::class, 'queue.connection');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerRouterBindings()
    {
        $this->singleton('router', function () {
            return $this->router;
        });
    }

    /**
     * Prepare the given request instance for use with the application.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \WPWCore\Http\Request
     */
    protected function prepareRequest(SymfonyRequest $request)
    {
        if (!$request instanceof Request) {
            $request = Request::createFromBase($request);
        }

        $request->setUserResolver(function ($guard = null) {
            return $this->make('auth')->guard($guard)->user();
        })->setRouteResolver(function () {
            return $this->currentRoute;
        });

        return $request;
    }

    /**
     * Register container bindings for the PSR-7 request implementation.
     *
     * @return void
     */
    protected function registerPsrRequestBindings()
    {
        $this->singleton(ServerRequestInterface::class, function ($app) {
            if (class_exists(Psr17Factory::class) && class_exists(PsrHttpFactory::class)) {
                $psr17Factory = new Psr17Factory;

                return (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))
                    ->createRequest($app->make('request'));
            }

            throw new BindingResolutionException('Unable to resolve PSR request. Please install symfony/psr-http-message-bridge and nyholm/psr7.');
        });
    }

    /**
     * Register container bindings for the PSR-7 response implementation.
     *
     * @return void
     */
    protected function registerPsrResponseBindings()
    {
        $this->singleton(ResponseInterface::class, function () {
            if (class_exists(PsrResponse::class)) {
                return new PsrResponse;
            }

            throw new BindingResolutionException('Unable to resolve PSR response. Please install nyholm/psr7.');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerTranslationBindings()
    {
        $this->singleton('translator', function () {
            $this->configure('app');

            $this->instance('path.lang', $this->getLanguagePath());

            $this->register(TranslationServiceProvider::class);

            return $this->make('translator');
        });
    }

    /**
     * Get the path to the application's language files.
     *
     * @return string
     */
    protected function getLanguagePath()
    {
        if (is_dir($langPath = $this->basePath() . '/resources/lang')) {
            return $langPath;
        } else {
            return __DIR__ . '/../resources/lang';
        }
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerUrlGeneratorBindings()
    {
        $this->singleton('url', function () {
            return new Routing\UrlGenerator($this);
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerValidatorBindings()
    {
        $this->singleton('validator', function () {
            $this->register(ValidationServiceProvider::class);

            return $this->make('validator');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerViewBindings()
    {
        $this->singleton('view', function () {
            return $this->loadComponent('view', ViewServiceProvider::class);
        });
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param string $config
     * @param array|string $providers
     * @param string|null $return
     * @return mixed
     */
    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);

        foreach ((array)$providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param string|null $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config') . '/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__ . '/../../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config') . '/' . $name . '.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__ . '/../../config/' . $name . '.php')) {
                return $path;
            }
        }
    }

    /**
     * Register the facades for the application.
     *
     * @param bool $aliases
     * @param array $userAliases
     * @return void
     */
    public function withFacades($aliases = true, $userAliases = [])
    {
        Facade::setFacadeApplication($this);

        if ($aliases) {
            $this->withAliases($userAliases);
        }
    }

    /**
     * Register the aliases for the application.
     *
     * @param array $userAliases
     * @return void
     */
    public function withAliases($userAliases = [])
    {
        $defaults = [
            \WPWhales\Support\Facades\Auth::class      => 'Auth',
            \WPWhales\Support\Facades\Cache::class     => 'Cache',
            \WPWhales\Support\Facades\DB::class        => 'DB',
            \WPWhales\Support\Facades\Event::class     => 'Event',
            \WPWhales\Support\Facades\Gate::class      => 'Gate',
            \WPWhales\Support\Facades\Log::class       => 'Log',
            \WPWhales\Support\Facades\Queue::class     => 'Queue',
            \WPWhales\Support\Facades\Route::class     => 'Route',
            \WPWhales\Support\Facades\Schema::class    => 'Schema',
            \WPWhales\Support\Facades\Storage::class   => 'Storage',
            \WPWhales\Support\Facades\URL::class       => 'URL',
            \WPWhales\Support\Facades\Validator::class => 'Validator',
        ];

        if (!static::$aliasesRegistered) {
            static::$aliasesRegistered = true;

            $merged = array_merge($defaults, $userAliases);

            foreach ($merged as $original => $alias) {
                if (!class_exists($alias)) {
                    class_alias($original, $alias);
                }
            }
        }
    }

    /**
     * Load the Eloquent library for the application.
     *
     * @return void
     */
    public function withEloquent()
    {
        $this->make('db');
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * Get the base path for the application.
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        if (isset($this->basePath)) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the database directory.
     *
     * @param string $path
     * @return string
     */
    public function databasePath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'database' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the language files.
     *
     * @param string $path
     * @return string
     */
    public function langPath($path = '')
    {
        return $this->getLanguagePath() . ($path != '' ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Get the storage path for the application.
     *
     * @param string|null $path
     * @return string
     */
    public function storagePath($path = '')
    {

        return WP_CONTENT_DIR . "/wpwhales";
    }

    /**
     * Set the storage directory.
     *
     * @param string $path
     * @return $this
     */
    public function useStoragePath($path)
    {
        $this->storagePath = $path;

        $this->instance('path.storage', $path);

        return $this;
    }

    /**
     * Get the path to the resources directory.
     *
     * @param string|null $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Determine if the application events are cached.
     *
     * @return bool
     */
    public function eventsAreCached()
    {
        return false;
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return defined("PHPUNIT_COMPOSER_INSTALL") || defined("WP_TESTS_DOMAIN");
    }

    /**
     * Prepare the application to execute a console command.
     *
     * @param bool $aliases
     * @return void
     */
    public function prepareForConsoleCommand($aliases = true)
    {

        $this->withFacades($aliases);
        $this->withEloquent();


//        $this->make('cache');
//        $this->make('queue');

        $this->configure('database');

        $this->instance('request', $this[\WPWCore\Http\Request::class]);

        $this->register(MigrationServiceProvider::class);
        $this->register(ConsoleServiceProvider::class);
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (!is_null($this->namespace)) {
            return $this->namespace;
        }


        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        foreach ((array)\WPWCore\Collections\data_get($composer, 'autoload.psr-4')
                 as $namespace => $path) {
            foreach ((array)$path as $pathChoice) {
                if (realpath(app()->path()) == realpath(base_path() . '/' . $pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function flush()
    {
        parent::flush();

        $this->middleware = [];
        $this->currentRoute = [];
        $this->loadedProviders = [];
        $this->routeMiddleware = [];
        $this->reboundCallbacks = [];
        $this->resolvingCallbacks = [];
        $this->availableBindings = [];
        $this->ranServiceBinders = [];
        $this->loadedConfigurations = [];
        $this->afterResolvingCallbacks = [];

        $this->router = null;
        $this->dispatcher = null;
        static::$instance = null;
        static::$aliasesRegistered = false;
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Get the current application fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this['config']->get('app.fallback_locale');
    }

    /**
     * Set the current application locale.
     *
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);
        $this['translator']->setLocale($locale);
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @param string $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Register a terminating callback with the application.
     *
     * @param callable|string $callback
     * @return $this
     */
    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }


    /**
     * Register a shutting down callback with the application.
     *
     * @param callable|string $callback
     * @return $this
     */
    public function shutting_down($callback)
    {
        $this->shutdownCallbacks[] = $callback;

        return $this;
    }


    /**
     * Register a shutdown callback with the application.
     *
     * @param callable|string $callback
     * @return $this
     */
    public function shutdown()
    {

        foreach ($this->shutdownCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * Terminate the application.
     *
     * @return void
     */
    public function terminate()
    {
        $index = 0;

        while ($index < count($this->terminatingCallbacks)) {
            $this->call($this->terminatingCallbacks[$index]);

            $index++;
        }
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            \WPWhales\Contracts\Foundation\Application::class    => 'app',
            \WPWhales\Contracts\Auth\Factory::class              => 'auth',
            \WPWhales\Contracts\Auth\Guard::class                => 'auth.driver',
            \WPWhales\Contracts\Cache\Factory::class             => 'cache',
            \WPWhales\Contracts\Cache\Repository::class          => 'cache.store',
            \WPWhales\Contracts\Config\Repository::class         => 'config',
            \WPWhales\Config\Repository::class                   => 'config',
            \WPWhales\Container\Container::class                 => 'app',
            \WPWhales\Contracts\Container\Container::class       => 'app',
            \WPWCore\Database\ConnectionResolverInterface::class => 'db',
            \WPWCore\Database\DatabaseManager::class             => 'db',
            \WPWhales\Contracts\Encryption\Encrypter::class      => 'encrypter',
            \WPWhales\Contracts\Events\Dispatcher::class         => 'events',
            \WPWhales\Contracts\Filesystem\Factory::class        => 'filesystem',
            \WPWhales\Contracts\Filesystem\Filesystem::class     => 'filesystem.disk',
            \WPWhales\Contracts\Filesystem\Cloud::class          => 'filesystem.cloud',
            \WPWhales\Contracts\Hashing\Hasher::class            => 'hash',
            'log'                                                => \Psr\Log\LoggerInterface::class,
            \WPWhales\Contracts\Queue\Factory::class             => 'queue',
            \WPWhales\Contracts\Queue\Queue::class               => 'queue.connection',
            \WPWhales\Redis\RedisManager::class                  => 'redis',
            \WPWhales\Contracts\Redis\Factory::class             => 'redis',
            \WPWhales\Redis\Connections\Connection::class        => 'redis.connection',
            \WPWhales\Contracts\Redis\Connection::class          => 'redis.connection',
            'request'                                            => \WPWCore\Http\Request::class,
            \WPWCore\Routing\Router::class                       => 'router',
            \WPWhales\Contracts\Translation\Translator::class    => 'translator',
            \WPWCore\Routing\UrlGenerator::class                 => 'url',
            \WPWhales\Contracts\Validation\Factory::class        => 'validator',
            \WPWhales\Contracts\View\Factory::class              => 'view',
            \WPWhales\Session\SessionManager::class              => 'session',
            \WPWhales\Contracts\Cookie\Factory::class            => 'cookie',
            \WPWhales\Contracts\Cookie\QueueingFactory::class    => 'cookie',
        ];
    }

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        'auth'                                           => 'registerAuthBindings',
        'auth.driver'                                    => 'registerAuthBindings',
        \WPWhales\Auth\AuthManager::class                => 'registerAuthBindings',
        \WPWhales\Contracts\Auth\Guard::class            => 'registerAuthBindings',
        \WPWhales\Contracts\Auth\Access\Gate::class      => 'registerAuthBindings',
        'cache'                                          => 'registerCacheBindings',
        'cache.store'                                    => 'registerCacheBindings',
        \WPWhales\Contracts\Cache\Factory::class         => 'registerCacheBindings',
        \WPWhales\Contracts\Cache\Repository::class      => 'registerCacheBindings',
        'composer'                                       => 'registerComposerBindings',
        'config'                                         => 'registerConfigBindings',
        'db'                                             => 'registerDatabaseBindings',
        \WPWCore\Database\Eloquent\Factory::class        => 'registerDatabaseBindings',
        'filesystem'                                     => 'registerFilesystemBindings',
        'filesystem.cloud'                               => 'registerFilesystemBindings',
        'filesystem.disk'                                => 'registerFilesystemBindings',
        \WPWhales\Contracts\Filesystem\Cloud::class      => 'registerFilesystemBindings',
        \WPWhales\Contracts\Filesystem\Filesystem::class => 'registerFilesystemBindings',
        \WPWhales\Contracts\Filesystem\Factory::class    => 'registerFilesystemBindings',
        'encrypter'                                      => 'registerEncrypterBindings',
        \WPWhales\Contracts\Encryption\Encrypter::class  => 'registerEncrypterBindings',
        'events'                                         => 'registerEventBindings',
        \WPWhales\Contracts\Events\Dispatcher::class     => 'registerEventBindings',
        'files'                                          => 'registerFilesBindings',
        'hash'                                           => 'registerHashBindings',
        \WPWhales\Contracts\Hashing\Hasher::class        => 'registerHashBindings',
        'log'                                            => 'registerLogBindings',
        \Psr\Log\LoggerInterface::class                  => 'registerLogBindings',
        'router'                                         => 'registerRouterBindings',
        \Psr\Http\Message\ServerRequestInterface::class  => 'registerPsrRequestBindings',
        \Psr\Http\Message\ResponseInterface::class       => 'registerPsrResponseBindings',
        'translator'                                     => 'registerTranslationBindings',
        'url'                                            => 'registerUrlGeneratorBindings',
        'validator'                                      => 'registerValidatorBindings',
        \WPWhales\Contracts\Validation\Factory::class    => 'registerValidatorBindings',
        'view'                                           => 'registerViewBindings',
        \WPWhales\Contracts\View\Factory::class          => 'registerViewBindings',
        'view.engine'                                    => 'registerViewBindings',
        'view.engine.resolver'                           => 'registerViewBindings',
        'session'                                        => 'registerSessionBindings',
        'session.store'                                  => 'registerSessionBindings',
        'WPWhales\Session\SessionManager'                => 'registerSessionBindings',
        'cookie'                                         => 'registerCookieBindings',
        'WPWhales\Contracts\Cookie\Factory'              => 'registerCookieBindings',
        'WPWhales\Contracts\Cookie\QueueingFactory'      => 'registerCookieBindings',
        'assets'                                         => 'registerAssetsBindings',
        'assets.manifest'                                => 'registerAssetsBindings'
    ];
}
