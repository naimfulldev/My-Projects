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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fuzedevc_afd-sales' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         ',`Xz!qF:+d[H/k(.(~}=IDT?RpN(``^jI13qxz2J`asx 1BAKK%SVl!s0IPD^?Uc' );
define( 'SECURE_AUTH_KEY',  'OqGVDr{H)DZ.37aOuU3$>NydOEt0^}l8:*A}?@3o1ksHyb6WUD34MWzFvebjp(q^' );
define( 'LOGGED_IN_KEY',    '68hv&<4xF@=xGz=|mils`!]~NM-t>5WX,YLui {2Mngw^EwpbZ9%W(K6<RvZ|Va{' );
define( 'NONCE_KEY',        'T^D+(_7.e:jR6SOR9m-it`1%3Go&~&-$=o>4=ic!&XNj^G_Mjp>h,.n%IFCz(f,2' );
define( 'AUTH_SALT',        'hOv.&t[:6b*UVt<x?QaFL2D?K|9Fa$k2Fo-lBsDsqyC](T1z}?MSntP.v&l&VlV0' );
define( 'SECURE_AUTH_SALT', 'v;%YI_eRXh4::oOW,3lNCMnuKcars-DMbAa|oDcJFWemV/)M{f~X6N(Kkw@35Xog' );
define( 'LOGGED_IN_SALT',   'ix?%T18|Yh%^5ez^i3-q)fUAEjL#Ni,P S{0;OFrY6[iVU;*ff1D4_bSMaSd8ty{' );
define( 'NONCE_SALT',       'b#H>{|WmQ2p#N_*l):@Un2A/4IEGv4p^=@fM{Zu*BmRqr&jY:1&,x2M>lBp$9zBq' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'afd_sales_';

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
