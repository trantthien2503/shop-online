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
define( 'DB_NAME', 'shop-online' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
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
define( 'AUTH_KEY',         'OXz%A]+a~Gw5WkM2i*of](Dlktn_G8||G;%=^-opmY<!jJO 3y!Z?QNB{3d2m.2)' );
define( 'SECURE_AUTH_KEY',  'TR4;#v2kt>1X|kVMH9QRCKvjPmlaqE:iX4`J$<i18:aKf2{{pwI FXrN+/STO=tB' );
define( 'LOGGED_IN_KEY',    '@a4oi|Drh5@iVP+nM@5L0_T jR}<Yr!{8b=R)8&FO]&gGaV&pON?/nyPTds8aj$z' );
define( 'NONCE_KEY',        'OuQ?<pQ^d}<7L8]2v`d=Sgp?/*DG$O=<S/P,*kw2kX*ShedHIu7ga<71!Vn7 #,_' );
define( 'AUTH_SALT',        'a&%a<=Ur dU^@dL*6kG^,YkSr.)!Q/)mUy}/aR!)w.`15BNX89 YRH^TQvbQwfQ,' );
define( 'SECURE_AUTH_SALT', 'I4G$KpBIJ6,U:p*S2/{Cp:s~fSpdEb}zqyHr+x0)GV/.r%1T8CT~B/zq#;)jRl9I' );
define( 'LOGGED_IN_SALT',   'AK@~uIa8FU3t+iu~]fP* !9i*kh%Lr1CYZxQ}e9&u{O}Dt }_Nc?jZ}&Z%~HnQX?' );
define( 'NONCE_SALT',       'yU-nBx8~q?cIa4|czx~Id=oe0yC|U],D9%o`Kx@U2|.De<{/<avTzQo!Ycc$KWp=' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
