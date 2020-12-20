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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'shuad' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'r>7TWk3bN+q)p{^UiH(:;ep(HoRys$8g=mU)~/$5^>.%N2^)Qy,o8fEAm[:*<l9s' );
define( 'SECURE_AUTH_KEY',  ']Cs~Q3#FJg}C)]ZxbNzI  tpUzxA:Nsva~1v^.ArzY2:K1@|DT;%$aL,.~grsTSN' );
define( 'LOGGED_IN_KEY',    '++.yEYw2)M:y _W;qhYWjP&uvxOF3L$|b9 Uj)AlOHt24~b#^(Fj*ix.#LRpi[_q' );
define( 'NONCE_KEY',        'oEPPhZqt$N0QFNBMmEJw?a?=ds^bB}V!:4~*]dW_04Dgx{IV#5^umLPr<f%wvhnN' );
define( 'AUTH_SALT',        'w.z,g=CWbN?^Gt_[hC?U Q#DQb|^/Es Culii;Iz*i9x}An<?{lRjm)V0ZMsvqv[' );
define( 'SECURE_AUTH_SALT', '2mJ%{/U8;Sf1Tyr[@Mbb}n@[9{sue~7aNF>NNHkH]^C0$B@v~fuV<K`_L_O)TKN.' );
define( 'LOGGED_IN_SALT',   'dCH`hq]?fqp5?+68Bj`z2tD&M/SiY@8yReS5qa=$lDRsQR4v?UaDnX+;k*IdQY%X' );
define( 'NONCE_SALT',       'f]GsG,Fsl+@NPani=IvD9e~O?LT8mYAkKKHy{?h=?]dw?^mVKi2KxX0dP{y@P6Wj' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'sd_';

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
