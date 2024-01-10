<?php

namespace Tests\Session;


use Carbon\Carbon;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\Cookie;
use WPWCore\Cookie\CookieJar;
use WPWCore\Http\Request;
use WPWCore\Session\SessionManager;
use WPWCore\Session\StartSession;
use WPWhales\Support\Facades\Date;
use WPWhales\Support\Facades\Schema;
use Mockery as m;
use WPWhales\Testing\TestResponse;

class SessionInitTest extends \WP_UnitTestCase
{


    protected static $files_directory = null;

    public function set_up()
    {
        parent::set_up();
        self::$files_directory = \WPWCore\config();
        $_COOKIE = [];
    }




    public function tear_down()
    {
        parent::tear_down();

        m::close();
    }

    public static function tear_down_after_class()
    {

        parent::tear_down_after_class(); // TODO: Change the autogenerated stub


        $directory = self::$files_directory["session"]["files"];
        $ignoreFiles = ['.gitignore', '.', '..'];

        $files = scandir($directory);

        foreach ($files as $file) {
            if (!in_array($file, $ignoreFiles)) {
                unlink($directory . '/' . $file);
            }
        }
    }


    public function test_cookies_are_attached_in_response()
    {
        $app = $this->app;
        $this->app->router->get("/cookie_test", [
            function () use ($app) {


                return 123;
            }
        ]);

        /**
         * @var $response TestResponse
         */
        $response = $this->call("GET", "/cookie_test");

        $response->assertCookie(\WPWCore\config("session.cookie_guest"));

    }


    public function test_start_multiple_sessions_without_saving_the_cookie()
    {

        $session = $this->createSession();
        $session->save();

        $session_1 = $session->getId();

        $session = $this->createSession();
        $session->save();

        $session_2 = $session->getId();


        $this->assertNotEquals($session_1, $session_2);

    }

    public function test_start_multiple_sessions_with_saving_the_cookie()
    {

        $session = $this->createSession();
        $session->save();

        $session_1 = $session->getId();

        $_COOKIE[$session->getName()] = $session->getId();

        $session = $this->createSession();

        $session->put("something_to_test_in_dependent_test", true);

        $session->save();
        $session_2 = $session->getId();


        $this->assertEquals($session_1, $session_2);
        $this->assertEquals($session->token(), $session->token());

        return [$_COOKIE, $session->token()];
    }

    /**
     * @depends test_start_multiple_sessions_with_saving_the_cookie
     * @return void
     *
     */
    public function test_persistant_value_check($params)
    {

        $_COOKIE = $params[0];

        $token = $params[1];

        $session = $this->createSession();

        $this->assertEquals($session->token(), $token);
        $this->assertTrue($session->get("something_to_test_in_dependent_test"));


    }

    public function test_session_data_without_saving()
    {
        $session = $this->createSession();
        $session->put("something", true);

        $this->assertTrue($session->get("something"));
        return [$session->getName(), $session->getId()];
    }

    /**
     * @depends test_session_data_without_saving
     * @return void
     */
    public function test_session_data_without_saving_dependant($cookie)
    {

        $_COOKIE[$cookie[0]] = $cookie[1];

        $session = $this->createSession();


        $this->assertNull($session->get("something"));
    }


    public function test_session_gets_saved_in_shutdown_hook()
    {
        global $wp_filter;
        $this->app["request"] = Request::capture();

        $this->app->initSession();

        $sessionInstance = $this->getMockBuilder(SessionManager::class)->setConstructorArgs([$this->app])->addMethods(["save"])->getMock();

        $sessionInstance->expects($this->exactly(2))->method("save");
        $this->app["session"] = $sessionInstance;

        //Mock the session cookie in the request
        $this->app["request"]->cookies->set($sessionInstance->getName(),$sessionInstance->getId());

        foreach ($wp_filter["shutdown"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "shutdown")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }
        }


        //now the hook will not invoke the save session
        $this->app["request"]->cookies->remove($sessionInstance->getName());

        foreach ($wp_filter["shutdown"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "shutdown")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }
        }

        //let's check it with request having cookie name but invalid value
        //Mock the session cookie in the request
        $this->app["request"]->cookies->set($sessionInstance->getName(),"INVALID VALUE");

        foreach ($wp_filter["shutdown"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "shutdown")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }
        }


        //remove the cookie from request and let's try with
        //cookie sent instance if it is queued
        $this->app["request"]->cookies->remove($sessionInstance->getName());

        $this->assertInstanceOf(Cookie::class,$this->app["cookie"]->queued($sessionInstance->getName()));
        $this->app["cookie"]->unqueue($sessionInstance->getName());
        $this->app["cookie"]->sentCookies[] = $sessionInstance->getName();

        $this->assertTrue($this->app["cookie"]->isCookieSent($sessionInstance->getName()));

        foreach ($wp_filter["shutdown"]->callbacks[10] as $key => $hook) {
            if (str_ends_with($key, "shutdown")) {
                $hook["function"][0]->{$hook["function"][1]}();
            }
        }

    }



    private function createSession()
    {
        $this->app["request"] = Request::capture();
        $session = new StartSession($this->app->make('session'));
        $session->handle();

        return $this->app->make('session');

    }


}
