<?php

namespace WPWCore\Auth;


use WPWCore\Models\User;
use WPWCore\Http\Request;
use WPWhales\Contracts\Auth\Guard;
use WPWhales\Contracts\Auth\UserProvider;
use WPWhales\Support\Facades\DB;
use function WPWCore\app;

class WordpressGuard implements Guard
{

    use GuardHelpers;


    /**
     * The request instance.
     *
     * @var \WPWCore\Http\Request
     */
    protected $request;


    /**
     * Create a new authentication guard.
     *
     * @param \WPWCore\Http\Request $request
     * @param \WPWhales\Contracts\Auth\UserProvider $provider
     * @return void
     */
    public function __construct(Request $request, UserProvider $provider)
    {

        $this->request = $request;
        $this->provider = $provider;

    }


    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {


    }


    protected function isLoggedIn(){

        return is_user_logged_in();
    }
    /**
     * Get the currently authenticated user.
     *
     * @return \WPWhales\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {

        if (!$this->isLoggedIn()) {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $wpUser = wp_get_current_user();




        $user = app(User::class);
        $user->setRawAttributes($wpUser->to_array());

        $this->user = $user;

        return $this->user;

    }


}
