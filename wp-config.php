<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'database' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'F5qcZ-UZ1>qBl2s^!%aUlU{,Dz-o_]n8x^Zt9TYn54e1oJ$NsNT:LZl;J,Pw.QT ' );
define( 'SECURE_AUTH_KEY',  'A:%@k#*4k|!@`AU8$ISL.,Q/Eka`a#Bx#GF^Vl}BgCq^@K4UpktQ$%vH2L1G.+k]' );
define( 'LOGGED_IN_KEY',    'E=.c+pxZOtv>`3jnoVL4LTdv3;K-uWaH3PuxoY9uNF9C.BD?`ZTc~k99|L[`~=05' );
define( 'NONCE_KEY',        'f{kNFX|~r*]%R-[ZgLDySBFoQfzz5X>fp)i6C3,RB;:dFuzBc#V,NAjjfMvi THw' );
define( 'AUTH_SALT',        'v%uK3CuS>N2b&dR-|Xe]G1yiu:yAfJu_yD=#G`*aXe4J4=LinnF=aku6;t)iy,$o' );
define( 'SECURE_AUTH_SALT', 'NHF09L9%]4;MBH(J4{=P25f2,zll{P3&Uu{D@qKa?a^xjOhcTL=PugLycr+wEijl' );
define( 'LOGGED_IN_SALT',   '~N`;X/PYOB~OfAic%}[$rB&JFQf|7y8szha2!SMn*#/fqx:x`;qlk:VJ{{&THMq+' );
define( 'NONCE_SALT',       'K:N<=Q()2x^ywo5%mQmbl64}?VxR/esHnWC6y^Al^r!9cEa|>X1,w~cs3hjA5~P_' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp1_';
define("WP_PLUGIN_DIR","/app/plugins");
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

define( 'WP_DEBUG_LOG', true );

define("DUSK_TESTING_ENVIRONMENT",true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
