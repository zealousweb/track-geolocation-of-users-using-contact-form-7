<?php
/**
 * Plugin Name: Track Geolocation Of Users Using Contact Form 7
 * Plugin URL: https://wordpress.org/plugins/track-geolocation-of-users-using-contact-form-7/
 * Description: Fetch Geolocation of user when user submits contact form 7.
 * Version: 2.0
 * Author: ZealousWeb
 * Author URI: https://www.zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: support@zealousweb.com
 * Text Domain: track-geolocation-of-users-using-contact-form-7
 * Domain Path: /languages
 *
 * Copyright: © 2009-2021 ZealousWeb Technologies.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

if ( !defined( 'CFGEO_VERSION' ) ) {
	define( 'CFGEO_VERSION', '2.0' ); // Version of plugin
}

if ( !defined( 'CFGEO_FILE' ) ) {
	define( 'CFGEO_FILE', __FILE__ ); // Plugin File
}

if ( !defined( 'CFGEO_DIR' ) ) {
	define( 'CFGEO_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if ( !defined( 'CFGEO_URL' ) ) {
	define( 'CFGEO_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if ( !defined( 'CFGEO_PLUGIN_BASENAME' ) ) {
	define( 'CFGEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( !defined( 'CFGEO_META_PREFIX' ) ) {
	define( 'CFGEO_META_PREFIX', 'cfgeo_' ); // Plugin metabox prefix
}

if ( !defined( 'CFGEO_PREFIX' ) ) {
	define( 'CFGEO_PREFIX', 'tglcf' ); // Plugin prefix
}

if ( !defined( 'CFGEO_POST_TYPE' ) ) {
	define( 'CFGEO_POST_TYPE', 'cfgeozw_data' ); // Plugin post type
}
/**
 * Initialize the main class
 */
if ( !function_exists( 'CFGEO' ) ) {

	if ( is_admin() ) {
		require_once( CFGEO_DIR . '/inc/admin/class.' . CFGEO_PREFIX . '.admin.php' );
		require_once( CFGEO_DIR . '/inc/admin/class.' . CFGEO_PREFIX . '.admin.action.php' );
		require_once( CFGEO_DIR . '/inc/admin/class.' . CFGEO_PREFIX . '.admin.filter.php' );
	}

	require_once( CFGEO_DIR . '/inc/lib/class.' . CFGEO_PREFIX . '.lib.php' );

	//Initialize all the things.
	require_once( CFGEO_DIR . '/inc/class.' . CFGEO_PREFIX . '.php' );
}
