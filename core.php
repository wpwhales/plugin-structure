<?php
/**
 * Plugin Name: WPWCORE Helpers
 * Plugin URI: https://wpwhales.io
 * Description: A DI based helpers for plugins developed by wpwhales.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Author: WPWhales
 * Author URI: https://wpwhales.io
 * GitHub Plugin URI: https://github.com/wpwhales
 */

if(!defined("ABSPATH")) exit;

require __DIR__."/vendor/autoload.php";


define("WPWCORE_LOADED",true);

do_action("WPWCORE_LOADED");