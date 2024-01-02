<?php

// change the next line to points to your wordpress dir
define( 'ABSPATH', '/app/' );

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// WARNING WARNING WARNING!
// tests DROPS ALL TABLES in the database. DO NOT use a production database

define( 'DB_NAME', 'wptest' );
define( 'DB_USER', 'wptest' );
define( 'DB_PASSWORD', 'wptest' );
define( 'DB_HOST', 'database' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // Only numbers, letters, and underscores please!

define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );