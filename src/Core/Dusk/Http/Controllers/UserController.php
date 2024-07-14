<?php

namespace WPWCore\Dusk\Http\Controllers;

use WPWCore\Http\Request;
use WPWhales\Support\Facades\Auth;
use WPWhales\Support\Facades\Session;
use WPWhales\Support\Str;
use function WPWCore\response;

class UserController
{
    /**
     * Retrieve the authenticated user identifier and class name.
     *
     * @param  string|null  $guard
     * @return array
     */
    public function user(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return [
            'id' => $user->getAuthIdentifier(),
            'className' => get_class($user),
        ];
    }

    /**
     * Login using the given user ID / email.
     *
     * @param  string  $userId
     * @param  string|null  $guard
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request , $userId)
    {

        wp_set_current_user($userId);
        wp_set_auth_cookie($userId,true);
        return response("Done",200);
    }

    /**
     * Log the user out of the application.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request ,)
    {

        wp_logout();

        return response("Done", 200);
    }


}
