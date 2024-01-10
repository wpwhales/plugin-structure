<?php

namespace WPWCore\Session;

use Closure;
use WPWhales\Contracts\Session\Session;
use WPWCore\Http\Request;
use WPWCore\Routing\Route;
use WPWCore\Session\SessionManager;
use WPWhales\Support\Carbon;
use WPWhales\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class StartSession
{
    /**
     * The session manager.
     *
     * @var \WPWhales\Session\SessionManager
     */
    protected $manager;


    /**
     * Create a new session middleware.
     *
     * @param \WPWCore\Session\SessionManager $manager
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->sessionConfigured()) {
            return false;
        }


        $request = \WPWCore\app("request");

        $session = $this->getSession($request);
        // If a session driver has been configured, we will need to start the session here
        // so that the data is ready for an application. Note that the Laravel sessions
        // do not make use of PHP "native" sessions in any way since they are crappy.
        $request->setLaravelSession(
            $this->startSession($request, $session)
        );

        $this->collectGarbage($session);


        $this->storeCurrentUrl($request, $session);

        $this->addCookieToQueue($session);




    }


    /**
     * Start the session for the given request.
     *
     * @param \WPWhales\Http\Request $request
     * @param \WPWhales\Contracts\Session\Session $session
     * @return \WPWhales\Contracts\Session\Session
     */
    protected function startSession(Request $request, $session)
    {
        return \WPWCore\tap($session, function ($session) use ($request) {
            $session->setRequestOnHandler($request);

            $session->start();
        });
    }

    /**
     * Get the session implementation from the manager.
     *
     * @param \WPWhales\Http\Request $request
     * @return \WPWhales\Contracts\Session\Session
     */
    public function getSession(Request $request)
    {
        return \WPWCore\tap($this->manager->driver(), function ($session) use ($request) {
            $session->setId($request->cookies->get($session->getName()));
        });
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param \WPWhales\Contracts\Session\Session $session
     * @return void
     */
    protected function collectGarbage(Session $session)
    {
        $config = $this->manager->getSessionConfig();

        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param array $config
     * @return bool
     */
    protected function configHitsLottery(array $config)
    {
        return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \WPWhales\Http\Request $request
     * @param \WPWhales\Contracts\Session\Session $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {

        if ($request->isMethod('GET') &&
            !$request->ajax() &&
            !$request->prefetch() &&
            !$request->isPrecognitive()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param \WPWhales\Contracts\Session\Session $session
     * @return void
     */
    protected function addCookieToQueue(Session $session)
    {
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
            \WPWCore\app("cookie")->queue(new Cookie(
                $session->getName(),
                $session->getId(),
                $this->getCookieExpirationDate(),
                $config['path'],
                $config['domain'],
                $config['secure'] ?? false,
                $config['http_only'] ?? true,
                false,
                $config['same_site'] ?? null,
                $config['partitioned'] ?? false
            ));
        }
    }

    /**
     * Save the session data to storage.
     *
     * @param \WPWhales\Http\Request $request
     * @return void
     */
    protected function saveSession($request)
    {
        if (!$request->isPrecognitive()) {
            $this->manager->driver()->save();
        }
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds()
    {
        return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config['expire_on_close'] ? 0 : Date::instance(
            Carbon::now()->addRealMinutes($config['lifetime'])
        );
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        return !is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @param array|null $config
     * @return bool
     */
    protected function sessionIsPersistent(array $config = null)
    {
        $config = $config ?: $this->manager->getSessionConfig();

        return !is_null($config['driver'] ?? null);
    }


}
