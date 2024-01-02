<?php


// path to test lib bootstrap.php
$test_lib_bootstrap_file = dirname( __FILE__ ) . '/includes/bootstrap.php';

if ( ! file_exists( $test_lib_bootstrap_file ) ) {
    echo PHP_EOL . "Error : unable to find " . $test_lib_bootstrap_file . PHP_EOL;
    exit( '' . PHP_EOL );
}

// set plugin and options for activation
$GLOBALS[ 'wp_tests_options' ] = array(

);

// call test-lib's bootstrap.php
require_once dirname( __FILE__ ) . '/includes/trait-plugin-application.php';
require_once dirname( __FILE__ ) . '/includes/trait-wp-makes-http-requests.php';

require_once $test_lib_bootstrap_file;



echo PHP_EOL;
echo 'Using Wordpress core : ' . ABSPATH . PHP_EOL;
echo PHP_EOL;