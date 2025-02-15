<?php

namespace Tests\Session;


use Carbon\Carbon;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\Cookie;
use WPWCore\Cookie\CookieJar;
use WPWCore\Http\Request;
use WPWCore\Session\SessionManager;
use WPWCore\Session\StartSession;
use WPWCore\Database\Schema\Blueprint;

use Mockery as m;
use WPWhales\Testing\TestResponse;

class DatabaseSessionTest extends \WP_UnitTestCase
{


    public function set_up()
    {
        parent::set_up();


        $this->app["session"];


        $schema = $this->app["db"]->getSchemaBuilder();
        if(!$schema->hasTable(\WPWCore\config("session.table"))){
            $schema->create(\WPWCore\config("session.table"), function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }


    }




    public function tear_down()
    {
        parent::tear_down();

        m::close();
    }

    public static function tear_down_after_class()
    {

        parent::tear_down_after_class(); // TODO: Change the autogenerated stub



    }



    public function test_session_is_saved_in_database(){

        $session = $this->app->make('session');
        $this->app["config"]->set("session.driver","database");
        $config = $this->app["config"];

        $session = $this->createSession();

        $session->save();

        $this->seeInDatabase($config->get("session.table"),[
            "id"=>$session->getId(),
            "user_id"=>0
        ]);
    }

    public function test_session_is_saved_with_user_id_for_logged_in_user_in_database(){

        $session = $this->app->make('session');
        $this->app["config"]->set("session.driver","database");
        $config = $this->app["config"];

        $session = $this->createSession();

        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);

        $session->save();

        $this->seeInDatabase($config->get("session.table"),[
            "id"=>$session->getId(),
            "user_id"=>$user_id
        ]);
    }

    private function createSession()
    {
        $this->app["request"] = Request::capture();
        $session = new StartSession($this->app->make('session'));
        $session->handle();

        return $this->app->make('session');

    }


}
