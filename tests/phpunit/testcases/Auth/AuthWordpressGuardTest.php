<?php

namespace Tests\Auth;


use WPWCore\Auth\AuthenticationException;
use WPWCore\Models\User;
use WPWhales\Support\Facades\Auth;

class AuthWordpressGuardTest extends \WP_UnitTestCase
{

    protected $wordpressGuard = null;

    public function set_up()
    {
        parent::set_up();

        $this->app->withFacades();
        $this->app->withEloquent();


    }

    public function test_user_model_gets_populated_with_wordpress_user_data()
    {
        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);
        $user = wp_get_current_user();
        $auth_user = Auth::user();
        foreach ($user->data as $key => $value) {
            $this->assertEquals($auth_user->{$key}, $value);
        }

    }

    public function test_guest_user()
    {
        $this->assertTrue(Auth::guest());
        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);
        $this->assertFalse(Auth::guest());


    }

    public function test_authenticate_method_expect_exception_if_not_logged_in()
    {

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage("Unauthenticated.");
        Auth::authenticate();

        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);

        $this->assertInstanceOf(User::class, Auth::authenticate());

    }


    public function test_user_instance_in_check_method()
    {
        $this->assertFalse(Auth::check());

        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);
        $this->assertTrue(Auth::check());

    }

    public function test_has_user_method()
    {

        $this->assertFalse(Auth::hasUser());
        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);

        Auth::user();
        $this->assertTrue(Auth::hasUser());

    }
    public function test_guard_id_method()
    {

        $this->assertNull(Auth::id());
        $user_id = $this->factory()->user->create();
        wp_set_current_user($user_id);

        $this->assertEquals(Auth::id() , $user_id);

    }
}
