<?php
/**
 * CFGEO_Admin_Filter Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CFGEO_Admin_Filter' ) ) {
	/**
	 *  The CFGEO_Admin_Filter Class
	 */
	class CFGEO_Admin_Filter {

		function __construct() {

			add_filter( 'post_row_actions',                                    array( $this, 'filter__cfgeo_post_row_actions' ), 10, 3 );
			add_filter( 'manage_edit-'.CFGEO_POST_TYPE.'_sortable_columns',    array( $this, 'filter__cfgeo_manage_cfgeozw_data_sortable_columns' ), 10, 3 );
			add_filter( 'manage_'.CFGEO_POST_TYPE.'_posts_columns',            array( $this, 'filter__cfgeo_manage_cfgeozw_data_posts_columns' ), 10, 3 );
			add_filter( 'bulk_actions-edit-'.CFGEO_POST_TYPE,                  array( $this, 'filter__cfgeo_bulk_actions_edit_cfgeozw_data' ) );
			add_filter( 'plugin_action_links_'.CFGEO_PLUGIN_BASENAME,          array( $this, 'filter__cfgeo__admin_plugin_links'), 10, 2 );

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

		/**
		 * Filter: post_row_actions
		 *
		 * - Used to modify the post list action buttons.
		 *
		 * @method filter__cfgeo_post_row_actions
		 *
		 * @param  array $actions
		 *
		 * @return array
		 */
		function filter__cfgeo_post_row_actions( $actions ) {

			if ( get_post_type() === CFGEO_POST_TYPE ) {
				unset( $actions['view'] );
				unset( $actions['inline hide-if-no-js'] );
			}

			return $actions;
		}

		/**
		 * Filter: manage_edit-cfgeozw_data_sortable_columns
		 *
		 * - Used to add the sortable fields into "cfgeozw_data" CPT
		 *
		 * @method filter__cfgeo_manage_cfgeozw_data_sortable_columns
		 *
		 * @param  array $columns
		 *
		 * @return array
		 */
		function filter__cfgeo_manage_cfgeozw_data_sortable_columns( $columns ) {
			$columns['country'] = '_country';
			return $columns;
		}

		/**
		 * Filter: manage_cfgeozw_data_posts_columns
		 *
		 * - Used to add new column fields for the "cfgeozw_data" CPT
		 *
		 * @method filter__cfgeo_manage_cfgeozw_data_posts_columns
		 *
		 * @param  array $columns
		 *
		 * @return array
		 */
		function filter__cfgeo_manage_cfgeozw_data_posts_columns( $columns ) {
			unset( $columns['date'] );
			$columns['country']         = __( 'Country', 'track-geolocation-of-users-using-contact-form-7' );
			$columns['state']           = __( 'State', 'track-geolocation-of-users-using-contact-form-7' );
			$columns['lat_long']         = __( 'Lat./Long.', 'track-geolocation-of-users-using-contact-form-7' );
			$columns['api_key_used']    = __( 'API', 'track-geolocation-of-users-using-contact-form-7' );
			$columns['date']            = __( 'Submitted Date', 'track-geolocation-of-users-using-contact-form-7' );
			return $columns;
		}

		/**
		 * Filter: bulk_actions-edit-cfgeozw_data
		 *
		 * - Add/Remove bulk actions for "cfgeozw_data" CPT
		 *
		 * @method filter__cfgeo_bulk_actions_edit_cfgeozw_data
		 *
		 * @param  array $actions
		 *
		 * @return array
		 */
		function filter__cfgeo_bulk_actions_edit_cfgeozw_data( $actions ) {
			unset( $actions['edit'] );
			return $actions;
		}

		/**
		 * Filter: admin_plugin_links
		 *
		 * - Add Plugin Setting and other link in plugin list page
		 *
		 * @method filter__cfgeo__admin_plugin_links
		 *
		 * @param  array $actions
		 *
		 * @return html
		 */
		function filter__cfgeo__admin_plugin_links( $links, $file ) {
			if ( $file != CFGEO_PLUGIN_BASENAME ) {
				return $links;
			}

			if ( ! current_user_can( 'wpcf7_read_contact_forms' ) ) {
				return $links;
			}

			$settingPage = admin_url("admin.php?page=geolocation-setting");

			$settingpageLink = '<a  href="'.$settingPage.'">' . __( 'Settings Page', 'track-geolocation-of-users-using-contact-form-7' ) . '</a>';
			array_unshift( $links , $settingpageLink);

			$documentLink = '<a target="_blank" href="https://www.zealousweb.com/wordpress-plugins/track-geolocation-of-users-using-contact-form-7/">' . __( 'Document Link', 'track-geolocation-of-users-using-contact-form-7' ) . '</a>';
			array_unshift( $links , $documentLink);

			return $links;
		}

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
		CFGEO()->admin->filter = new CFGEO_Admin_Filter;
	} );
}
