<?php
if(!defined("ABSPATH")) die();


$router->get('/login/{userId}', [
    'uses' => 'WPWCore\Dusk\Http\Controllers\UserController@login',
    'as' => 'dusk.login',
]);
$router->get('/logout', [
    'uses' => 'WPWCore\Dusk\Http\Controllers\UserController@logout',
    'as' => 'dusk.logout',
]);
$router->get('/user', [
    'uses' => 'WPWCore\Dusk\Http\Controllers\UserController@user',
    'as' => 'dusk.user',
]);

$router->get("/forced_verify_email",[function(\WPWCore\Http\Request $request){

    $email = $request->get("email");
    $user_id = \FiteCard\Models\User::where("user_email",$email)->first()->ID;

    update_user_meta($user_id,"email_verified",time());

    return \WPWCore\redirect("/");
}]);
