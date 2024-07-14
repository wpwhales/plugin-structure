<?php

namespace WPWCore\Dusk;

use Exception;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use WPWCore\Testing\TestCase as  BaseTestCase;
use WPWCore\Dusk\Chrome\SupportsChrome;
use WPWCore\Dusk\Concerns\ProvidesBrowser;
use function WPWCore\base_path;

abstract class TestCase extends BaseTestCase
{
    use ProvidesBrowser, SupportsChrome;

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Browser::$baseUrl = $this->baseUrl();

        Browser::$storeScreenshotsAt = base_path('tests/Browser/screenshots');

        Browser::$storeConsoleLogAt = base_path('tests/Browser/console');

        Browser::$storeSourceAt = base_path('tests/Browser/source');

        Browser::$userResolver = function () {
            return $this->user();
        };
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()
        );
    }

    /**
     * Determine the application's base URL.
     *
     * @return string
     */
    protected function baseUrl()
    {
        return rtrim("https://plugin-pattern.lndo.site/", '/');
    }

    /**
     * Return the default user to authenticate.
     *
     * @return \App\User|int|null
     *
     * @throws \Exception
     */
    protected function user()
    {
        throw new Exception('User resolver has not been set.');
    }

    /**
     * Determine if the tests are running within Laravel Sail.
     *
     * @return bool
     */
    protected static function runningInSail()
    {
        return isset($_ENV['LARAVEL_SAIL']) && $_ENV['LARAVEL_SAIL'] == '1';
    }


    /**
     * Determine if the tests are running within Lando.
     *
     * @return bool
     */
    protected static function runningInLando()
    {

        return isset($_ENV['LANDO']) && $_ENV['LANDO'] == 'ON';
    }
}
