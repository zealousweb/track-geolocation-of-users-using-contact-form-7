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

		private static $cfgeo_instance    = null;

				static $cfgeo_setting_page = 'geolocation-setting';

		var $admin = null,
			$front = null,
			$lib   = null;

		public static function instance() {

			if ( is_null( self::$cfgeo_instance ) )
				self::$cfgeo_instance = new self();

			return self::$cfgeo_instance;
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
			$cfgeo_lang_dir = dirname( CFGEO_PLUGIN_BASENAME ) . '/languages/';
			$cfgeo_lang_dir = apply_filters( 'CFGEO_languages_directory', $cfgeo_lang_dir );

			# Traditional WordPress plugin locale filter.
			$cfgeo_get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$cfgeo_get_locale = get_user_locale();
			}

			# Traditional WordPress plugin locale filter
			$cfgeo_locale = apply_filters( 'plugin_locale',  $cfgeo_get_locale, 'track-geolocation-of-users-using-contact-form-7' );
			$cfgeo_mofile = sprintf( '%1$s-%2$s.mo', 'track-geolocation-of-users-using-contact-form-7', $cfgeo_locale );

			# Setup paths to current locale file
			$cfgeo_mofile_global = WP_LANG_DIR . '/plugins/' . basename( CFGEO_DIR ) . '/' . $cfgeo_mofile;

			if ( file_exists( $cfgeo_mofile_global ) ) {
				# Look in global /wp-content/languages/plugin-name folder
				load_textdomain( 'track-geolocation-of-users-using-contact-form-7', $cfgeo_mofile_global );
			} else {
				# Load the default language files
				load_plugin_textdomain( 'track-geolocation-of-users-using-contact-form-7', false, $cfgeo_lang_dir );
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

			$cfgeo_labels = array(
				'name' => esc_html__( 'Geolocation Details', 'track-geolocation-of-users-using-contact-form-7' ),
				'singular_name' => esc_html__( 'Geolocation Detail', 'track-geolocation-of-users-using-contact-form-7' ),
				'edit_item' => esc_html__( 'Edit Submission', 'track-geolocation-of-users-using-contact-form-7' ),
				'view_item' => esc_html__( 'View Submission', 'track-geolocation-of-users-using-contact-form-7' ),
				'search_items' =>esc_html__( 'Search Submissions', 'track-geolocation-of-users-using-contact-form-7' ),
				'not_found' => esc_html__( 'No Submissions Found', 'track-geolocation-of-users-using-contact-form-7' ),
				'not_found_in_trash' => esc_html__( 'No Submissions Found in Trash', 'track-geolocation-of-users-using-contact-form-7' ),
			);

			$cfgeo_args = array(
				'label' => esc_html__( 'Geolocation Details', 'track-geolocation-of-users-using-contact-form-7' ),
				'labels' => $cfgeo_labels,
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

			register_post_type( CFGEO_POST_TYPE, $cfgeo_args );
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
						wp_redirect( admin_url( 'admin.php?page=' . self::$cfgeo_setting_page ) );
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
