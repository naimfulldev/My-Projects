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
define( 'DB_NAME', 'fuzedevc_your_mind_center' );

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
define( 'AUTH_KEY',         '92}&?UlNVMMi_5p5JZ!rA8KA{hrQX>_=dZ_]4f#J:Mw6q_2dXD,*F2=qI?fRM{i^' );
define( 'SECURE_AUTH_KEY',  'j(hc][EuL~`ycDEve*+{p$.$?CZ|4wC5dtKMJL(sW-6(?h{}h=)<TQq(S9m$T2na' );
define( 'LOGGED_IN_KEY',    '<@X~+M-fBihRH3 uL]x<L71Fw^k%zCb(&e/>z({1NGof[H4*qEa, v!$8B8ADW B' );
define( 'NONCE_KEY',        'aA`PJ% LRV(5j@%5[Sw-;b<Ze7n7[6:-I]$o!]AgBsJaK~Jx.TM} IxFx>gg-vF0' );
define( 'AUTH_SALT',        'p%g{O&9>4,TV}Iq}i<JyufVtGe<uz`rh?n0%tDP3J8v/Kh;Y0GZnR4udJZ5~H&um' );
define( 'SECURE_AUTH_SALT', 'Vg$W(gK5^MU^0B~i2{2=Sw3SW^I|?Aw$Cw>oT>A@hQScBEZ+!{:(h{-MLHbqNt6]' );
define( 'LOGGED_IN_SALT',   'Shk4._j<q-[_sr!WWb@d*U%3-P[n[MD=?~ZUe!3:*ZD 5,&h8u035q0F:u&vonR3' );
define( 'NONCE_SALT',       '=4-;FIB?VM?y_t39)#lWYR<>GEjFu1f^MOx;||g>b|2DEaS,M9-l=Tm;w8M`FkP3' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'your_mind_center_';

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
