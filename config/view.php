<?php
if(!defined("ABSPATH")) exit;

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        \WPWCore\resource_path('views'),
        dirname(__FILE__,2)."/resources/views"
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */
    'cache' => true,
    'compiled' => WP_CONTENT_DIR."/wpwhales/".(defined("WPW_VIEWS_CACHED_KEY") ? WPW_VIEWS_CACHED_KEY : hash('xxh128', 'SOMERANDOMSTRING')),

];
