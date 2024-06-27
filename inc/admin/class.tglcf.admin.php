<?php
/**
 * CFGEO_Admin Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CFGEO_Admin' ) ) {

	/**
	 * The CFGEO_Admin Class
	 */
	class CFGEO_Admin {

		var $action = null,
			$filter = null;

		function __construct() {
			add_action( 'admin_menu',    array( $this, 'action__CFGEO_admin_menu' ) );
		}

		/*
		   ###     ######  ######## ####  #######  ##    ##  ######
		  ## ##   ##    ##    ##     ##  ##     ## ###   ## ##    ##
		 ##   ##  ##          ##     ##  ##     ## ####  ## ##
		##     ## ##          ##     ##  ##     ## ## ## ##  ######
		######### ##          ##     ##  ##     ## ##  ####       ##
		##     ## ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##     ##  ######     ##    ####  #######  ##    ##  ######
		*/

		/**
		 * [action__CFGEO_admin_menu Adding Menu in Admin]
		 */
		function action__CFGEO_admin_menu() {
			remove_meta_box( 'submitdiv', CFGEO_POST_TYPE, 'side' );
		}

		/*
		######## #### ##       ######## ######## ########   ######
		##        ##  ##          ##    ##       ##     ## ##    ##
		##        ##  ##          ##    ##       ##     ## ##
		######    ##  ##          ##    ######   ########   ######
		##        ##  ##          ##    ##       ##   ##         ##
		##        ##  ##          ##    ##       ##    ##  ##    ##
		##       #### ########    ##    ######## ##     ##  ######
		*/


		/*
		######## ##     ## ##    ##  ######  ######## ####  #######  ##    ##  ######
		##       ##     ## ###   ## ##    ##    ##     ##  ##     ## ###   ## ##    ##
		##       ##     ## ####  ## ##          ##     ##  ##     ## ####  ## ##
		######   ##     ## ## ## ## ##          ##     ##  ##     ## ## ## ##  ######
		##       ##     ## ##  #### ##          ##     ##  ##     ## ##  ####       ##
		##       ##     ## ##   ### ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##        #######  ##    ##  ######     ##    ####  #######  ##    ##  ######
		*/

	}

	add_action( 'plugins_loaded', function() {
		cfgeo()->admin = new CFGEO_Admin;
	} );
}
