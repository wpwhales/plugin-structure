<?php

namespace WPWCore\Dusk\Concerns;

use WPWCore\Dusk\Browser;
use PHPUnit\Framework\Assert as PHPUnit;
use function WPWCore\config;
use function WPWCore\route;

trait InteractsWithAuthentication
{
    static protected $messageSent = false;
    protected $message = "Please define the constant 'DUSK_TESTING_ENVIRONMENT' in your wp-config.php file to use the loginAs method for browser testing.";

    /**
     * Log into the application as the default user.
     *
     * @return $this
     */
    public function login()
    {
        return $this->loginAs(call_user_func(Browser::$userResolver));
    }

    /**
     * Log into the application using a given user ID or email.
     *
     * @param object|string $userId
     * @param string|null $guard
     * @return $this
     */
    public function loginAs($userId, $guard = null)
    {


        $userId = is_object($userId) && method_exists($userId, 'getKey') ? $userId->getKey() : $userId;


        if (!static::$messageSent) {
            $border = str_repeat('*', strlen($this->message) + 4);
            $formattedMessage = PHP_EOL . $border . PHP_EOL . "* " . $this->message . " *" . PHP_EOL . $border . PHP_EOL;
            fwrite(STDOUT, $formattedMessage);

            static::$messageSent = true;
        }


        return $this->visit(rtrim(route('dusk.login', ['userId' => $userId])));
    }

    /**
     * Log out of the application.
     *
     * @param string|null $guard
     * @return $this
     */
    public function logout($guard = null)
    {
        return $this->visit(rtrim(route('dusk.logout'), '/'));
    }

    /**
     * Get the ID and the class name of the authenticated user.
     *
     * @param string|null $guard
     * @return array
     */
    protected function currentUserInfo($guard = null)
    {
        $response = $this->visit(route('dusk.user'));

        return json_decode(strip_tags($response->driver->getPageSource()), true);
    }

    /**
     * Assert that the user is authenticated.
     *
     * @param string|null $guard
     * @return $this
     */
    public function assertAuthenticated($guard = null)
    {
        $currentUrl = $this->driver->getCurrentURL();

        PHPUnit::assertNotEmpty($this->currentUserInfo($guard), 'The user is not authenticated.');

        return $this->visit($currentUrl);
    }

    /**
     * Assert that the user is not authenticated.
     *
     * @param string|null $guard
     * @return $this
     */
    public function assertGuest($guard = null)
    {
        $currentUrl = $this->driver->getCurrentURL();

        PHPUnit::assertEmpty(
            $this->currentUserInfo($guard), 'The user is unexpectedly authenticated.'
        );

        return $this->visit($currentUrl);
    }

    /**
     * Assert that the user is authenticated as the given user.
     *
     * @param mixed $user
     * @param string|null $guard
     * @return $this
     */
    public function assertAuthenticatedAs($user, $guard = null)
    {
        $currentUrl = $this->driver->getCurrentURL();

        $expected = [
            'id'        => $user->getAuthIdentifier(),
            'className' => get_class($user),
        ];

        PHPUnit::assertSame(
            $expected, $this->currentUserInfo($guard),
            'The currently authenticated user is not who was expected.'
        );

        return $this->visit($currentUrl);
    }

    /**
     * Determine if route() should use an absolute path.
     *
     * @return bool
     */
    private function shouldUseAbsoluteRouteForAuthentication()
    {
        return config('dusk.domain') !== null;
    }
}
