<?php
/**
 * CFGEO Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'CFGEO' ) ) {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	/**
	 * The main CFGEO class
	 */
	class CFGEO {

		private static $_instance    = null;

				static $setting_page = 'geolocation-setting';

		var $admin = null,
			$front = null,
			$lib   = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {

			add_action( 'admin_init',               array( $this, 'action__cfgeo_plugin_redirect'));
			add_action( 'plugins_loaded',           array( $this, 'action__cfgeo_plugins_loaded' ), 1 );
			# Register plugin activation hook
			register_activation_hook( CFGEO_FILE,   array( $this, 'action__cfgeo_activation' ) );

		}

		/**
		 * [action__cfgeo_plugins_loaded plugins_loaded]
		 *
		 */
		function action__cfgeo_plugins_loaded() {

			if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				add_action( 'admin_notices', array( $this, 'action__cfgeo_admin_notices_deactive' ) );
				deactivate_plugins( CFGEO_PLUGIN_BASENAME );
				if ( isset( $_GET['activate'] ) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )) {
					unset( $_GET['activate'] );
				}
			}

			# Action to load custom post type
			add_action( 'init', array( $this, 'action__cfgeo_init' ) );

			global $wp_version;

			# Set filter for plugin's languages directory
			$CFGEO_lang_dir = dirname( CFGEO_PLUGIN_BASENAME ) . '/languages/';
			$CFGEO_lang_dir = apply_filters( 'CFGEO_languages_directory', $CFGEO_lang_dir );

			# Traditional WordPress plugin locale filter.
			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			# Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale',  $get_locale, 'track-geolocation-of-users-using-contact-form-7' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'track-geolocation-of-users-using-contact-form-7', $locale );

			# Setup paths to current locale file
			$mofile_global = WP_LANG_DIR . '/plugins/' . basename( CFGEO_DIR ) . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'track-geolocation-of-users-using-contact-form-7', $mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'track-geolocation-of-users-using-contact-form-7', false, $CFGEO_lang_dir );
			}
		}

		/**
		 * Action: init
		 *
		 * - Register posttype
		 *
		 * @return [array] Amended post update messages with new CPT update messages.
		 */
		function action__cfgeo_init() {

			flush_rewrite_rules();

			/**
			 * Post Type: Geolocation cf7.
			 */

			$labels = array(
				'name' => __( 'Geolocation Details', 'track-geolocation-of-users-using-contact-form-7' ),
				'singular_name' => __( 'Geolocation Detail', 'track-geolocation-of-users-using-contact-form-7' ),
				'edit_item' => __( 'Edit Submission', 'track-geolocation-of-users-using-contact-form-7' ),
				'view_item' => __( 'View Submission', 'track-geolocation-of-users-using-contact-form-7' ),
				'search_items' => __( 'Search Submissions', 'track-geolocation-of-users-using-contact-form-7' ),
				'not_found' => __( 'No Submissions Found', 'track-geolocation-of-users-using-contact-form-7' ),
				'not_found_in_trash' => __( 'No Submissions Found in Trash', 'track-geolocation-of-users-using-contact-form-7' ),
			);

			$args = array(
				'label' => __( 'Geolocation Details', 'track-geolocation-of-users-using-contact-form-7' ),
				'labels' => $labels,
				'description' => '',
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'delete_with_user' => false,
				'show_in_rest' => false,
				'rest_base' => '',
				'has_archive' => false,
				'show_in_menu' => 'wpcf7',
				'show_in_nav_menus' => false,
				'exclude_from_search' => true,
				'capability_type' => 'post',
				'capabilities' => array(
					'read' => true,
					'create_posts'  => false,
					'publish_posts' => false,
				),
				'map_meta_cap' => true,
				'hierarchical' => false,
				'rewrite' => false,
				'query_var' => false,
				'supports' => array( 'title' ),
			);

			register_post_type( CFGEO_POST_TYPE, $args );
		}

		/**
		 * [action__cfgeo_admin_notices_deactive Message if CF7 plugin is not activated/installed]
		 * @return [type] [Error message]
		 */
		function action__cfgeo_admin_notices_deactive() {
			echo '<div class="error">' .
				'<p><strong><a href="' . esc_url( 'https://wordpress.org/plugins/contact-form-7/' ) . '" target="_blank">Contact Form 7</a></strong> is required to use <strong>' . esc_html( 'Track Geolocation Of Users Using Contact Form 7' ) . '</strong>.</p>' .
			'</div>';
		}

		/**
		 * register_activation_hook
		 *
		 * - When active plugin
		 *
		 */
		function action__cfgeo_activation() {
			if ( class_exists('WPCF7') ) {
				update_option('cfgeo_activation_redirect', 'yes');
			}
		}

		/**
		 *
		 * - When active plugin redirect to setting page
		 *
		 */
		function action__cfgeo_plugin_redirect() {
			if ( class_exists('WPCF7') ) {
				if (get_option('cfgeo_activation_redirect', false)) {
					delete_option('cfgeo_activation_redirect');
					if(!isset($_GET['activate-multi']) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' ))
					{
						wp_redirect( admin_url( 'admin.php?page=' . self::$setting_page ) );
					}
				}
			}
		}
	}
}

function CFGEO() {
	return CFGEO::instance();
}

CFGEO();
