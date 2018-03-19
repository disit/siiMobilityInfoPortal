<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'siimobility');

/** MySQL database username */
define('DB_USER', 'homestead');

/** MySQL database password */
define('DB_PASSWORD', 'secret');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', 'utf8_general_ci');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         's[QX}ZDbnca4!RsPHwrYBF_]2m?a0JCx2;e:>EX,Zw*_.):*%m[.#Hoc*{TC(V[M');
define('SECURE_AUTH_KEY',  'q;@K^O~jO@ful=W>jBe4bHi9j;Pv`-GDn[yHq&gO-xU~(_F[je=jvNZnT]58;^T?');
define('LOGGED_IN_KEY',    '4%My^c.?/<#%4iKJkJ{~kjqSEl&hLc9hj8W)v1nPyi8*/RV7Soz{u3C-`h1<d@ H');
define('NONCE_KEY',        '4bJh+]jJ3G==}~44sZL7cYnu1E/eko>^@5+0&SJ);96ukDPJ~7WJdNyf,iCjt2VU');
define('AUTH_SALT',        ')Rb#@oBO6(uax-vL+_,7|38NKl=QrU{(Tug*l-_()voho)(DS7>~=0eKcUG:^6cK');
define('SECURE_AUTH_SALT', '>L0T!#)ldijKEX/},Sbu--x?/+dg{/yiMDWv>}yIFTT)b <F@b%yS2VV,#.&{`W+');
define('LOGGED_IN_SALT',   '@G8N{2bRMwUa#vk|.p9~YpeN|/5&w >8nhveuh&kkbk98)#pGI6R}du)oxK@@+#)');
define('NONCE_SALT',       '-cKX]NV!fD%+o$@E0B6*6dX/T!kcS=r@hcr3/w+#Qd{7<82Gyj#rWU=TYu>PGkcp');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
