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
