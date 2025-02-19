<?php
/**
 * CFGEO_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CFGEO_Admin_Action' ) ) {

	/**
	 *  The CFGEO_Admin_Action Class
	 */
	class CFGEO_Admin_Action {

		function __construct()  {

			add_action( 'init',                                     array( $this, 'action__cfgeo_init_99' ), 99 );
			add_action( 'admin_init',                               array( $this, 'action__cfgeo_init' ) );
			add_action( 'add_meta_boxes',                           array( $this, 'action__cfgeo_add_meta_boxes' ) );
			add_action( 'manage_'.CFGEO_POST_TYPE.'_posts_custom_column',  array( $this, 'action__manage_cfgeozw_data_posts_custom_column' ), 10, 2 );
			add_action( 'pre_get_posts',                            array( $this, 'action__cfgeo_pre_get_posts' ) );
			add_action( 'restrict_manage_posts',                    array( $this, 'action__cfgeo_restrict_manage_posts' ) );
			add_action( 'parse_query',                              array( $this, 'action__cfgeo_parse_query' ) );
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
		 * Action: admin_init
		 *
		 * - Register admin min js and admin min css
		 *
		 */
		function action__cfgeo_init() {
			wp_register_style( CFGEO_PREFIX . '_admin_css', CFGEO_URL . 'assets/css/admin.min.css', array(), CFGEO_VERSION );
			wp_register_style( CFGEO_PREFIX . '_spectrum_css', CFGEO_URL . 'assets/css/spectrum.min.css', array(), CFGEO_VERSION );
			wp_register_script( CFGEO_PREFIX . '_spectrum_js', CFGEO_URL . 'assets/js/spectrum.min.js', array( 'jquery-core' ), CFGEO_VERSION,true);
			wp_register_script( CFGEO_PREFIX . '_admin_js', CFGEO_URL . 'assets/js/admin.min.js', array( 'jquery-core' ), CFGEO_VERSION,true);
			wp_register_script( CFGEO_PREFIX . '_graph_js', CFGEO_URL . 'assets/js/graph.min.js', array( 'jquery-core' ), CFGEO_VERSION,true);
			wp_register_script( CFGEO_PREFIX . '_loader_js', 'https://www.gstatic.com/charts/loader.js', array( 'jquery-core' ), CFGEO_VERSION,true);
		}

		/**
		 * [action__cfgeo_init_99 Used to perform the CSV export functionality.]
		 */
		function action__cfgeo_init_99() {

			if (isset( $_REQUEST['export_csv'] ) && isset( $_REQUEST['form-id'] ) && !empty( $_REQUEST['form-id'] && $_REQUEST['post_type'] == CFGEO_POST_TYPE) ) {
				$form_id = $_REQUEST['form-id'];

				if ( 'all' == $form_id ) {
					add_action( 'admin_notices', array( $this, 'action__cfgeodb_admin_notices_export' ) );
					return;
				}
				$args = array(
					'post_type' => CFGEO_POST_TYPE,
					'posts_per_page' => -1
				);

				$exported_data = get_posts( $args );
				if ( empty( $exported_data ) ){
					return;
				}

				/** CSV Export **/
				$filename = 'cfgeo-' . $form_id . '-' . time() . '.csv';
				/** Prepare CSV Header **/
				$data = unserialize( get_post_meta( $exported_data[0]->ID, '_form_data', true ) );
				$header_row = $this->csv_header($data);

				if( !empty( $exported_data ) ) {
					foreach ( $exported_data as $entry ) {
						$single_data = unserialize( get_post_meta( $entry->ID, '_form_data', true ) );
						if ($single_data !== false) {
							$row = array();
							foreach ( $single_data as $key => $value ) {
								if ( is_array( $value ) ) {
									$value = implode( ', ', $value );
								}

								if ( $key == '_form_id' ) {
									$meta_value = get_post_meta( $entry->ID, $key, true );

									if ( ! empty( $meta_value ) && '_form_id' == $key ) {
										$row[$key] = get_the_title( $meta_value );
									} else {
										$row[$key] = $meta_value;
									}
								} else {
									$row[$key] = $value;
								}
							}
						}

						$data_rows[] = array_map( 'sanitize_text_field', $row );
					}
				}

				ob_start();

				$fh = @fopen( 'php://output', 'w' );
				fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-type: text/csv; charset=UTF-8' );
				header( "Content-Disposition: attachment; filename={$filename}" );
				header( 'Expires: 0' );
				header( 'Pragma: public' );
				fputcsv( $fh, array_values(array_map('sanitize_text_field',$header_row)) );
				foreach ( $data_rows as $data_row ) {
					fputcsv( $fh, $data_row );
				}
				//fclose( $fh );

				ob_end_flush();
				die();

			}
		}

		/**
		 * [csv_header csv header title]
		 * @param  [type] $data [form row title]
		 * @return [array]       [csv row title]
		 */
		function csv_header($data){
			foreach ($data as $key => $value) {
				if($key == '_form_id'){
					$header[] = 'Form ID/Name';
				}else{
					$header_label = str_replace("cfgeo-", "", $key);
					$header[] = ucwords($header_label);
				}
			}
			return $header;
		}


		/**
		 * Action: add_meta_boxes
		 *
		 * - Add meta boxes for the CPT "cfgeozw_data"
		 */

		function action__cfgeo_add_meta_boxes() {
			add_meta_box( 'cfgeozw-data', __( 'From Data', 'track-geolocation-of-users-using-contact-form-7' ), array( $this, 'cfgeozw_show_from_data' ), CFGEO_POST_TYPE, 'normal', 'high' );
			add_meta_box( 'cfgeo-help', __( 'Do you need help for configuration?', 'track-geolocation-of-users-using-contact-form-7' ), array( $this, 'cfgeo_show_help_data' ), CFGEO_POST_TYPE, 'side', 'high' );
		}

		/**
		 * Action: manage_data_posts_custom_column
		 *
		 * @method manage_cfgeozw_data_posts_custom_column
		 *
		 * @param  string  $column
		 * @param  int     $post_id
		 *
		 * @return string
		 */
		function action__manage_cfgeozw_data_posts_custom_column( $column, $post_id ) {
			switch ( $column ) {
				case 'country' :
					$country = get_post_meta( $post_id , 'cfgeo-country', true );
					echo !empty( $country ) ? esc_html( $country ) : '';
					break;

				case 'state' :
					$state = get_post_meta( $post_id , 'cfgeo-state', true );
					echo !empty( $state ) ? esc_html( $state ) : '';
					break;

				case 'lat_long' :
					$lat_long = get_post_meta( $post_id , 'cfgeo-lat-long', true );
					echo !empty( $lat_long ) ? esc_html( $lat_long ) : '';
					break;

				case 'api_key_used' :
					$api_key_used = get_post_meta( $post_id , 'cfgeo-api-used', true );
					echo !empty( $api_key_used ) ? esc_html( $api_key_used ) : '';
					break;
			}
		}

		/**
		 * Action: pre_get_posts
		 *
		 * - Used to perform order by into CPT List.
		 *
		 * @method action__cfgeo_pre_get_posts
		 *
		 * @param  object $query WP_Query
		 */
		function action__cfgeo_pre_get_posts( $query ) {

			if (! is_admin() || !in_array ( $query->get( 'post_type' ), array( CFGEO_POST_TYPE ) )){
				return;
			}

			$orderby = $query->get( 'orderby' );

			if ( 'country' == $orderby ) {
				$query->set( 'meta_key', 'country' );
				$query->set( 'orderby', 'meta_value' );
			}
		}

		/**
		 * Action: restrict_manage_posts
		 *
		 * - Used to creat filter by form and export functionality.
		 *
		 * @method action__cfgeo_restrict_manage_posts
		 *
		 * @param  string $post_type
		 */
		function action__cfgeo_restrict_manage_posts( $post_type ) {

			if ( CFGEO_POST_TYPE != $post_type ) {
				return;
			}

			$posts = get_posts(
				array(
					'post_type'        => 'wpcf7_contact_form',
					'post_status'      => 'publish',
					'suppress_filters' => false,
					'posts_per_page'   => -1
				)
			);

			if ( empty( $posts ) ) {
				return;
			}

			$selected = ( isset( $_GET['form-id']) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' ) ) ? sanitize_text_field($_GET['form-id']) : '' ;

			echo '<select name="form-id" id="form-id">';
			echo '<option value="all">' . esc_html( 'Select Forms', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
			foreach ( $posts as $post ) {
				echo '<option value="' . esc_attr( $post->ID ) . '" ' . selected( $selected, $post->ID, false ) . '>' . esc_html( $post->post_title ) . '</option>';
			}
			echo '</select>';

			echo '<input type="submit" id="export_csv" name="export_csv" class="button action" value="'. esc_html( 'Export CSV', 'track-geolocation-of-users-using-contact-form-7' ) . '">';

		}

		/**
		 * Action: parse_query
		 *
		 * - Filter data by form id.
		 *
		 * @method action__cfgeo_parse_query
		 *
		 * @param  object $query WP_Query
		 */
		function action__cfgeo_parse_query( $query ) {
			if (! is_admin() || !in_array ( $query->get( 'post_type' ), array( CFGEO_POST_TYPE ) )){
				return;
			}

			if (is_admin() && isset( $_GET['form-id'] )	&& 'all' != $_GET['form-id'] ) {
				$query->query_vars['meta_value']   = sanitize_text_field($_GET['form-id']);
				$query->query_vars['meta_compare'] = '=';
			}

		}

		/**
		 * Action: admin_notices
		 *
		 * - Added use notice when trying to export without selecting the form.
		 *
		 * @method action__cfgeodb_admin_notices_export
		 */
		function action__cfgeodb_admin_notices_export() {
			echo '<div class="error">' .
				'<p>' .
					esc_html( 'Please select Form to export.', 'track-geolocation-of-users-using-contact-form-7' ) .
				'</p>' .
			'</div>';
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

		/**
		 * - Used to display the form data in CPT detail page.
		 *
		 * @method cfgeozw_show_from_data
		 *
		 * @param  object $post WP_Post
		 */

		function cfgeozw_show_from_data( $post ) {

			$meta = unserialize(get_post_meta( $post->ID, '_form_data', true ));
			if($meta['cfgeo-lat-long'] != ''){
				$lat_long_sep = explode(",",$meta['cfgeo-lat-long']);
			}
			echo '<table class="cfspzw-box-data form-table">' .
				'<style>.inside-field td, .inside-field th{ padding-top: 5px; padding-bottom: 5px;}</style>';

				if ( !empty( $meta['_form_id'] ) ) {
					echo '<tr class="form-field">' .
						'<th scope="row">' .
							'<label for="hcf_author">' . esc_html( 'Form ID/Name', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>' .
						'</th>' .
						'<td>' .
							(
								(
									$meta['_form_id'] != ''
									&& !empty( get_the_title( $meta['_form_id'] ) )
								)
								? esc_html( get_the_title( $meta['_form_id'] ) )
								: esc_html( $meta['_form_id'] )
							) .
						'</td>' .
					'</tr>';

					foreach ($meta as $key => $value) {
						if (strpos($key, '_') !== 0) {
							$label_name = str_replace('-', ' ', $key);
							$rmv_cfgeo = str_replace('cfgeo', '', $label_name);
							if (is_array($value)) {
								$value = implode(', ', $value);
							}

							echo '<tr class="form-field">' .
									'<th scope="row">' .
										'<label for="hcf_author">' . esc_html( sprintf( '%s', ucwords($rmv_cfgeo) ), 'track-geolocation-of-users-using-contact-form-7' ) . '</label>' .
									'</th>' .
									'<td>' . esc_html( $value ) .
									'</td>' .
								'</tr>';
						}
					}

					if (get_post_meta( $post->ID, 'cfgeo-debug-ipstack', true )) {
						echo '<tr class="form-field">' .
								'<th scope="row">' .
									'<label for="hcf_author">' . esc_html( 'Debug ipstack', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>' .
								'</th>' .
								'<td>' . esc_html( get_post_meta( $post->ID, 'cfgeo-debug-ipstack', true ) ) .
								'</td>' .
							'</tr>';
					}

					if (get_post_meta( $post->ID, 'cfgeo-debug-ipapi', true )) {
						echo '<tr class="form-field">' .
								'<th scope="row">' .
									'<label for="hcf_author">' . esc_html( 'Debug ipapi', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>' .
								'</th>' .
								'<td>' . esc_html( get_post_meta( $post->ID, 'cfgeo-debug-ipapi', true ) ) .
								'</td>' .
							'</tr>';
					}

					if (get_post_meta( $post->ID, 'cfgeo-debug-keycdn', true )) {
						echo '<tr class="form-field">' .
								'<th scope="row">' .
									'<label for="hcf_author">' . esc_html( 'Debug Keycdn', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>' .
								'</th>' .
								'<td>' . esc_html( get_post_meta( $post->ID, 'cfgeo-debug-keycdn', true ) ) .
								'</td>' .
							'</tr>';
					}
				}


			echo '</table>';
			echo '<!-- Entry Location metabox -->';
			echo '<div id="cf7-entry-geolocation" class="postbox">';

			echo '<h2 class="hndle"><span>' . esc_html( 'Location', 'track-geolocation-of-users-using-contact-form-7' ) . '</span></h2>';

			echo '<div class="inside">';

			if ( empty( $meta['cfgeo-lat-long'] ) ){

				echo '<p style="padding:0 10px 10px;">' . esc_html( 'Unable to load location data for this entry. This usually means CF7-Geolocation was unable to process the user\'s IP address or it is non-standard format.', 'track-geolocation-of-users-using-contact-form-7' ) . '</p>';

			}else{

				$map = add_query_arg(
					array(
						'q'      => $meta['cfgeo-city'] . ',' . $meta['cfgeo-state'],
						'll'     => $lat_long_sep[0] . ',' . $lat_long_sep[1],
						'z'      => 6,
						'output' => 'embed',
					),
					'https://maps.google.com/maps'
				);
				echo '<iframe frameborder="0" src="' . esc_url( $map ) . '" style="width:100%;height:420px;"></iframe>';
			}
		}

		/**
		 * - Used to add meta box in CPT detail page.
		 */
		function cfgeo_show_help_data() {
			echo '<div id="cf7geo-data-help">' .
				wp_kses_post(
					apply_filters(
						CFGEO_PREFIX . '/help/cfgeo_data/postbox',
						'<ol>' .
							'<li><a href="' . esc_url( 'https://store.zealousweb.com/track-geolocation-of-users-using-contact-form-7' ) . '" target="_blank">Refer the document.</a></li>' .
							'<li><a href="' . esc_url( 'mailto:support@zealousweb.com' ) . '" target="_blank">Contact Us</a></li>' .
						'</ol>'
					)
				) .
			'</div>';
		}
	}

	add_action( 'plugins_loaded', function() {
		cfgeo()->admin->action = new CFGEO_Admin_Action;
	} );
}
