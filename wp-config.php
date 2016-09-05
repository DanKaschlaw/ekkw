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
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', "db:3306");
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '2I f>]LTzbn|rVWRu@za9y-50K|lLS)jTdx+>*H1$%xWg#oyor-eE7C}j-QGh|)o');
define('SECURE_AUTH_KEY',  'ft|70Adv)B|do3GsBc2nj,aOTK/MX#{NmcxU&tG&(aED`nJ70njo3xNl!>:/_C7O');
define('LOGGED_IN_KEY',    '}`j ,AZpXWRv#C-||Y:f(F-@Ka&]]GU5UYCPujQ5<3vG;@X!SEr%cgW(}XjJX|)y');
define('NONCE_KEY',        '4!!hcy}%o2U6$rlYxhT*6VZ$aB=J_v%~oW1&X@oB!L@_hzzptzLX@]4a$k4!ro#V');
define('AUTH_SALT',        'k6}qaU27I5z~s0mq>S{K&Vb<67R?sEIG{S#gFkNxQ-zGWQ)NB~1wi^gWx@ia`yf}');
define('SECURE_AUTH_SALT', '&eH^2IwDs=POYU(uPulfe-{slWvgwk[-$od-G@/u%W<V~xF.!^9%xObe}{fWr3!A');
define('LOGGED_IN_SALT',   'i JhYvki~=|+U[Eemd}o|]}YcF,MO-9ooYPv=jm;=B1&//0)=3yF-9: ](ggg!<H');
define('NONCE_SALT',       'fG9+RO4v+0zGq:#ijL+LSV<nk&U-:+^J3aM+-f}I1 W]dXACCTv-Z6d:Vh8or<3F');

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
