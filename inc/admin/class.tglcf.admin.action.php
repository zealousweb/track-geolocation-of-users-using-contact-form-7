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
			add_action( 'admin_enqueue_scripts',                    array( $this, 'action__cfgeo_admin_enqueue_scripts' ) );
			add_action( 'add_meta_boxes',                           array( $this, 'action__cfgeo_add_meta_boxes' ) );
			add_action( 'manage_'.CFGEO_POST_TYPE.'_posts_custom_column',  array( $this, 'action__manage_cfgeozw_data_posts_custom_column' ), 10, 2 );
			add_action( 'pre_get_posts',                            array( $this, 'action__cfgeo_pre_get_posts' ) );
			add_action( 'restrict_manage_posts',                    array( $this, 'action__cfgeo_restrict_manage_posts' ) );
			add_action( 'parse_query',                              array( $this, 'action__cfgeo_parse_query' ) );
			
			// Add AJAX handlers for real-time filtering
			add_action( 'wp_ajax_cfgeo_filter_submissions',         array( $this, 'action__cfgeo_ajax_filter_submissions' ) );
			add_action( 'wp_ajax_nopriv_cfgeo_filter_submissions',  array( $this, 'action__cfgeo_ajax_filter_submissions' ) );
			
			// Add AJAX handlers for webhook functionality
			add_action( 'wp_ajax_cfgeo_test_webhook',               array( $this, 'action__cfgeo_ajax_test_webhook' ) );
			add_action( 'wp_ajax_cfgeo_get_webhook_logs',           array( $this, 'action__cfgeo_ajax_get_webhook_logs' ) );
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
		 * Action: admin_enqueue_scripts
		 *
		 * - Enqueue styles and scripts for CFGEO post type pages
		 *
		 */
		function action__cfgeo_admin_enqueue_scripts( $hook ) {
			$screen = get_current_screen();
			
			// Enqueue scripts for CFGEO post type pages or geolocation settings page
			if ( ($screen && $screen->post_type === CFGEO_POST_TYPE) || 
				 (isset($_GET['page']) && $_GET['page'] === 'geolocation-setting') ) {
				
				wp_enqueue_style( CFGEO_PREFIX . '_admin_css' );
				wp_enqueue_style( CFGEO_PREFIX . '_spectrum_css' );
				wp_enqueue_script( CFGEO_PREFIX . '_spectrum_js' );
				wp_enqueue_script( CFGEO_PREFIX . '_admin_js' );
				
				// Localize script for AJAX
				wp_localize_script( CFGEO_PREFIX . '_admin_js', 'cfgeo_ajax', array(
					'nonce' => wp_create_nonce( 'cfgeo_filter_nonce' ),
					'error_message' => __( 'An error occurred while filtering. Please try again.', 'track-geolocation-of-users-using-contact-form-7' ),
					'date_error_message' => __( 'From date cannot be later than To date.', 'track-geolocation-of-users-using-contact-form-7' ),
					'webhook_test_nonce' => wp_create_nonce( 'cfgeo_webhook_test_nonce' ),
					'webhook_logs_nonce' => wp_create_nonce( 'cfgeo_webhook_logs_nonce' )
				) );
			}
		}



		/**
		 * [action__cfgeo_init_99 Used to perform the CSV export functionality.]
		 */
		function action__cfgeo_init_99() {

			if (isset( $_REQUEST['export_csv'] ) && $_REQUEST['post_type'] == CFGEO_POST_TYPE) {

				// Build query args based on current filters
				$args = array(
					'post_type' => CFGEO_POST_TYPE,
					'posts_per_page' => -1,
					'meta_query' => array(),
					'date_query' => array()
				);

				// Form filter
				if ( isset( $_REQUEST['form-id'] ) && 'all' != $_REQUEST['form-id'] ) {
					$args['meta_query'][] = array(
						'key' => '_form_id',
						'value' => sanitize_text_field( $_REQUEST['form-id'] ),
						'compare' => '='
					);
				}

				// Country filter
				if ( isset( $_REQUEST['country-filter'] ) && !empty( $_REQUEST['country-filter'] ) ) {
					$args['meta_query'][] = array(
						'key' => 'cfgeo-country',
						'value' => sanitize_text_field( $_REQUEST['country-filter'] ),
						'compare' => '='
					);
				}

				// City filter
				if ( isset( $_REQUEST['city-filter'] ) && !empty( $_REQUEST['city-filter'] ) ) {
					$args['meta_query'][] = array(
						'key' => 'cfgeo-city',
						'value' => sanitize_text_field( $_REQUEST['city-filter'] ),
						'compare' => '='
					);
				}

				// Date range filter
				if ( isset( $_REQUEST['date-from'] ) || isset( $_REQUEST['date-to'] ) ) {
					$date_query = array();
					
					if ( !empty( $_REQUEST['date-from'] ) ) {
						$date_query['after'] = sanitize_text_field( $_REQUEST['date-from'] );
					}
					
					if ( !empty( $_REQUEST['date-to'] ) ) {
						$date_query['before'] = sanitize_text_field( $_REQUEST['date-to'] ) . ' 23:59:59';
					}
					
					if ( !empty( $date_query ) ) {
						$date_query['inclusive'] = true;
						$args['date_query'] = $date_query;
					}
				}

				// Search functionality
				if ( isset( $_REQUEST['search-term'] ) && !empty( $_REQUEST['search-term'] ) ) {
					$search_term = sanitize_text_field( $_REQUEST['search-term'] );
					$args['s'] = $search_term;
					$args['search-term'] = $search_term;
					// Also search in meta fields
					$args['meta_query'][] = array(
						'relation' => 'OR',
						array(
							'key' => 'cfgeo-country',
							'value' => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key' => 'cfgeo-city',
							'value' => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key' => 'cfgeo-state',
							'value' => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key' => 'cfgeo-lat-long',
							'value' => $search_term,
							'compare' => 'LIKE'
						),
						array(
							'key' => '_form_data',
							'value' => $search_term,
							'compare' => 'LIKE'
						),
					);
				}
				//print_r($args['meta_query'] );die();
				// Set meta query relation if we have multiple meta queries
				if ( count( $args['meta_query'] ) > 1 ) {
					$args['meta_query']['relation'] = 'AND';
				}
				$exported_data = get_posts( $args );
				
				if ( empty( $exported_data ) ){
					add_action( 'admin_notices', array( $this, 'action__cfgeodb_admin_notices_no_data' ) );
					return;
				}

				/** CSV Export **/
				$filename = 'cfgeo-export-' . date('Y-m-d-H-i-s') . '.csv';
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
		 * Get unique meta values for filter dropdowns
		 * @param  string $meta_key [meta key to get unique values for]
		 * @return array            [array of unique values]
		 */
		function get_unique_meta_values( $meta_key ) {
			global $wpdb;
			
			$results = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT meta_value 
					FROM {$wpdb->postmeta} pm 
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
					WHERE p.post_type = %s 
					AND pm.meta_key = %s 
					AND pm.meta_value != '' 
					ORDER BY meta_value ASC",
					CFGEO_POST_TYPE,
					$meta_key
				)
			);
			
			return $results;
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

				case 'city' :
					$city = get_post_meta( $post_id , 'cfgeo-city', true );
					echo !empty( $city ) ? esc_html( $city ) : '';
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
				$query->set( 'meta_key', 'cfgeo-country' );
				$query->set( 'orderby', 'meta_value' );
			} elseif ( 'state' == $orderby ) {
				$query->set( 'meta_key', 'cfgeo-state' );
				$query->set( 'orderby', 'meta_value' );
			} elseif ( 'city' == $orderby ) {
				$query->set( 'meta_key', 'cfgeo-city' );
				$query->set( 'orderby', 'meta_value' );
			} elseif ( 'lat_long' == $orderby ) {
				$query->set( 'meta_key', 'cfgeo-lat-long' );
				$query->set( 'orderby', 'meta_value' );
			} elseif ( 'api_key_used' == $orderby ) {
				$query->set( 'meta_key', 'cfgeo-api-used' );
				$query->set( 'orderby', 'meta_value' );
			}
		}

		/**
		 * Action: restrict_manage_posts
		 *
		 * - Used to create advanced filters by form, country, city, date range and export functionality.
		 *
		 * @method action__cfgeo_restrict_manage_posts
		 *
		 * @param  string $post_type
		 */
		function action__cfgeo_restrict_manage_posts( $post_type ) {

			if ( CFGEO_POST_TYPE != $post_type ) {
				return;
			}

			// Get all forms
			$posts = get_posts(
				array(
					'post_type'        => 'wpcf7_contact_form',
					'post_status'      => 'publish',
					'suppress_filters' => false,
					'posts_per_page'   => -1
				)
			);

			// Get unique countries and cities for filter dropdowns
			$countries = $this->get_unique_meta_values( 'cfgeo-country' );
			$cities = $this->get_unique_meta_values( 'cfgeo-city' );

			// Get current filter values
			$selected_form = isset( $_GET['form-id'] ) ? sanitize_text_field( $_GET['form-id'] ) : '';
			$selected_country = isset( $_GET['country-filter'] ) ? sanitize_text_field( $_GET['country-filter'] ) : '';
			$selected_city = isset( $_GET['city-filter'] ) ? sanitize_text_field( $_GET['city-filter'] ) : '';
			$date_from = isset( $_GET['date-from'] ) ? sanitize_text_field( $_GET['date-from'] ) : '';
			$date_to = isset( $_GET['date-to'] ) ? sanitize_text_field( $_GET['date-to'] ) : '';
			$search_term = isset( $_GET['search-term'] ) ? sanitize_text_field( $_GET['search-term'] ) : '';

			echo '<div class="cfgeo-advanced-filters">';
			
			// Loading indicator
			echo '<div class="cfgeo-loading"><div class="spinner is-active"></div></div>';
			
			// Hidden inputs for AJAX functionality
			echo '<input type="hidden" name="orderby" value="' . esc_attr( isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'date' ) . '">';
			echo '<input type="hidden" name="order" value="' . esc_attr( isset( $_GET['order'] ) ? $_GET['order'] : 'DESC' ) . '">';
			echo '<input type="hidden" name="paged" value="' . esc_attr( isset( $_GET['paged'] ) ? $_GET['paged'] : '1' ) . '">';
			
			// Search input
			echo '<div class="cfgeo-filter-row">';
			echo '<label for="search-term">' . esc_html__( 'Search:', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>';
			echo '<input type="text" name="search-term" id="search-term" value="' . esc_attr( $search_term ) . '" placeholder="' . esc_attr__( 'Search by any field...', 'track-geolocation-of-users-using-contact-form-7' ) . '" style="width: 200px;">';
			echo '</div>';

			// Form filter
			echo '<div class="cfgeo-filter-row">';
			echo '<label for="form-id">' . esc_html__( 'Form:', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>';
			echo '<select name="form-id" id="form-id" style="width: 200px;">';
			echo '<option value="all">' . esc_html__( 'All Forms', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
			if ( !empty( $posts ) ) {
			foreach ( $posts as $post ) {
					echo '<option value="' . esc_attr( $post->ID ) . '" ' . selected( $selected_form, $post->ID, false ) . '>' . esc_html( $post->post_title ) . '</option>';
				}
			}
			echo '</select>';
			echo '</div>';

			// Country filter
			echo '<div class="cfgeo-filter-row">';
			echo '<label for="country-filter">' . esc_html__( 'Country:', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>';
			echo '<select name="country-filter" id="country-filter" style="width: 200px;">';
			echo '<option value="">' . esc_html__( 'All Countries', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
			if ( !empty( $countries ) ) {
				foreach ( $countries as $country ) {
					echo '<option value="' . esc_attr( $country ) . '" ' . selected( $selected_country, $country, false ) . '>' . esc_html( $country ) . '</option>';
				}
			}
			echo '</select>';
			echo '</div>';

			// City filter
			echo '<div class="cfgeo-filter-row">';
			echo '<label for="city-filter">' . esc_html__( 'City:', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>';
			echo '<select name="city-filter" id="city-filter" style="width: 200px;">';
			echo '<option value="">' . esc_html__( 'All Cities', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
			if ( !empty( $cities ) ) {
				foreach ( $cities as $city ) {
					echo '<option value="' . esc_attr( $city ) . '" ' . selected( $selected_city, $city, false ) . '>' . esc_html( $city ) . '</option>';
				}
			}
			echo '</select>';
			echo '</div>';

			// Date range filters
			echo '<div class="cfgeo-filter-row">';
			echo '<label>' . esc_html__( 'Date Range:', 'track-geolocation-of-users-using-contact-form-7' ) . '</label>';
			echo '<input type="date" name="date-from" id="date-from" value="' . esc_attr( $date_from ) . '" placeholder="' . esc_attr__( 'From', 'track-geolocation-of-users-using-contact-form-7' ) . '" style="width: 150px; margin-right: 5px;">';
			echo '<input type="date" name="date-to" id="date-to" value="' . esc_attr( $date_to ) . '" placeholder="' . esc_attr__( 'To', 'track-geolocation-of-users-using-contact-form-7' ) . '" style="width: 150px;">';
			echo '</div>';

			// Filter and Export buttons
			echo '<div class="cfgeo-filter-buttons">';
			echo '<input type="submit" id="export_csv" name="export_csv" class="button action" value="' . esc_attr__( 'Export CSV', 'track-geolocation-of-users-using-contact-form-7' ) . '">';
			echo '<a href="#" class="button cfgeo-clear-filters">' . esc_html__( 'Clear Filters', 'track-geolocation-of-users-using-contact-form-7' ) . '</a>';
			echo '</div>';

			echo '</div>';

		}

		/**
		 * Action: parse_query
		 *
		 * - Filter data by form id, country, city, date range and search term.
		 *
		 * @method action__cfgeo_parse_query
		 *
		 * @param  object $query WP_Query
		 */
		function action__cfgeo_parse_query( $query ) {
			if (! is_admin() || !in_array ( $query->get( 'post_type' ), array( CFGEO_POST_TYPE ) )){
				return;
			}

			// Build meta query array
			$meta_query = array();

			// Form filter
			if (is_admin() && isset( $_GET['form-id'] ) && 'all' != $_GET['form-id'] ) {
				$meta_query[] = array(
					'key' => '_form_id',
					'value' => sanitize_text_field( $_GET['form-id'] ),
					'compare' => '='
				);
			}

			// Country filter
			if (is_admin() && isset( $_GET['country-filter'] ) && !empty( $_GET['country-filter'] ) ) {
				$meta_query[] = array(
					'key' => 'cfgeo-country',
					'value' => sanitize_text_field( $_GET['country-filter'] ),
					'compare' => '='
				);
			}

			// City filter
			if (is_admin() && isset( $_GET['city-filter'] ) && !empty( $_GET['city-filter'] ) ) {
				$meta_query[] = array(
					'key' => 'cfgeo-city',
					'value' => sanitize_text_field( $_GET['city-filter'] ),
					'compare' => '='
				);
			}

			// Date range filter
			if (is_admin() && ( isset( $_GET['date-from'] ) || isset( $_GET['date-to'] ) ) ) {
				$date_query = array();
				
				if ( !empty( $_GET['date-from'] ) ) {
					$date_query['after'] = sanitize_text_field( $_GET['date-from'] );
				}
				
				if ( !empty( $_GET['date-to'] ) ) {
					$date_query['before'] = sanitize_text_field( $_GET['date-to'] ) . ' 23:59:59';
				}
				
				if ( !empty( $date_query ) ) {
					$date_query['inclusive'] = true;
					$query->set( 'date_query', $date_query );
				}
			}

			// Search functionality
			if (is_admin() && isset( $_GET['search-term'] ) && !empty( $_GET['search-term'] ) ) {
				$search_term = sanitize_text_field( $_GET['search-term'] );
				
				// Search in post title and content
				$query->set( 's', $search_term );
				
				// Also search in meta fields
				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key' => 'cfgeo-country',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-city',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-state',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-lat-long',
						'value' => $search_term,
						'compare' => 'LIKE'
					)
				);
			}

			// Set meta query if we have any
			if ( !empty( $meta_query ) ) {
				if ( count( $meta_query ) > 1 ) {
					$meta_query['relation'] = 'AND';
				}
				$query->set( 'meta_query', $meta_query );
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
					esc_html__( 'Please select Form to export.', 'track-geolocation-of-users-using-contact-form-7' ) .
				'</p>' .
			'</div>';
		}

		/**
		 * Action: admin_notices
		 *
		 * - Added notice when no data found for export.
		 *
		 * @method action__cfgeodb_admin_notices_no_data
		 */
		function action__cfgeodb_admin_notices_no_data() {
			echo '<div class="notice notice-warning is-dismissible">' .
				'<p>' .
					esc_html__( 'No data found matching the current filters. Please adjust your filter criteria and try again.', 'track-geolocation-of-users-using-contact-form-7' ) .
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

		/**
		 * AJAX handler for real-time filtering
		 *
		 * @method action__cfgeo_ajax_filter_submissions
		 */
		function action__cfgeo_ajax_filter_submissions() {
			// Verify nonce
			if ( !wp_verify_nonce( $_POST['nonce'], 'cfgeo_filter_nonce' ) ) {
				error_log('CFGEO AJAX: Nonce verification failed');
				wp_die( 'Security check failed' );
			}

			// Check permissions
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( 'Insufficient permissions' );
			}

			// Build query args
			$args = array(
				'post_type' => CFGEO_POST_TYPE,
				'posts_per_page' => 20,
				'paged' => isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1,
				'meta_query' => array(),
				'date_query' => array()
			);

			// Form filter
			if ( isset( $_POST['form_id'] ) && 'all' != $_POST['form_id'] ) {
				$args['meta_query'][] = array(
					'key' => '_form_id',
					'value' => sanitize_text_field( $_POST['form_id'] ),
					'compare' => '='
				);
			}

			// Country filter
			if ( isset( $_POST['country_filter'] ) && !empty( $_POST['country_filter'] ) ) {
				$args['meta_query'][] = array(
					'key' => 'cfgeo-country',
					'value' => sanitize_text_field( $_POST['country_filter'] ),
					'compare' => '='
				);
			}

			// City filter
			if ( isset( $_POST['city_filter'] ) && !empty( $_POST['city_filter'] ) ) {
				$args['meta_query'][] = array(
					'key' => 'cfgeo-city',
					'value' => sanitize_text_field( $_POST['city_filter'] ),
					'compare' => '='
				);
			}

			// Date range filter
			if ( isset( $_POST['date_from'] ) || isset( $_POST['date_to'] ) ) {
				$date_query = array();
				
				if ( !empty( $_POST['date_from'] ) ) {
					$date_query['after'] = sanitize_text_field( $_POST['date_from'] );
				}
				
				if ( !empty( $_POST['date_to'] ) ) {
					$date_query['before'] = sanitize_text_field( $_POST['date_to'] ) . ' 23:59:59';
				}
				
				if ( !empty( $date_query ) ) {
					$date_query['inclusive'] = true;
					$args['date_query'] = $date_query;
				}
			}

			// Search functionality
			if ( isset( $_POST['search_term'] ) && !empty( $_POST['search_term'] ) ) {
				$search_term = sanitize_text_field( $_POST['search_term'] );
				$args['search_term'] = $search_term;
				// Also search in meta fields
				$args['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key' => 'cfgeo-country',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-city',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-state',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'cfgeo-lat-long',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					array(
						'key' => '_form_data',
						'value' => $search_term,
						'compare' => 'LIKE'
					),
					
				);
			}

			// Set meta query relation if we have multiple meta queries
			if ( count( $args['meta_query'] ) > 1 ) {
				$args['meta_query']['relation'] = 'AND';
			}

			// Handle sorting
			if ( isset( $_POST['orderby'] ) ) {
				$orderby = sanitize_text_field( $_POST['orderby'] );
				$order = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC';
				
				if ( in_array( $orderby, array( 'country', 'state', 'city', 'lat_long', 'api_key_used' ) ) ) {
					$args['meta_key'] = 'cfgeo-' . str_replace( '_', '-', $orderby );
					$args['orderby'] = 'meta_value';
					$args['order'] = $order;
				} else {
					$args['orderby'] = $orderby;
					$args['order'] = $order;
				}
			}

			// Get posts
			$query = new WP_Query( $args );
			$posts = $query->posts;
			$total_posts = $query->found_posts;

			// Generate HTML for table rows
			$html = '';
			if ( !empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$html .= '<tr id="post-' . $post->ID . '" class="iedit author-self level-0 post-' . $post->ID . ' type-' . CFGEO_POST_TYPE . ' status-publish hentry">';
					
					// Checkbox
					$html .= '<td class="check-column"><input type="checkbox" name="post[]" value="' . $post->ID . '"></td>';
					
					// Title
					$title = get_the_title( $post->ID );
					$html .= '<td class="post-title page-title column-title"><strong><a href="' . get_edit_post_link( $post->ID ) . '">' . esc_html( $title ) . '</a></strong></td>';
					
					// Country
					$country = get_post_meta( $post->ID, 'cfgeo-country', true );
					$html .= '<td class="country column-country">' . esc_html( $country ) . '</td>';
					
					// State
					$state = get_post_meta( $post->ID, 'cfgeo-state', true );
					$html .= '<td class="state column-state">' . esc_html( $state ) . '</td>';
					
					// City
					$city = get_post_meta( $post->ID, 'cfgeo-city', true );
					$html .= '<td class="city column-city">' . esc_html( $city ) . '</td>';
					
					// Lat/Long
					$lat_long = get_post_meta( $post->ID, 'cfgeo-lat-long', true );
					$html .= '<td class="lat_long column-lat_long">' . esc_html( $lat_long ) . '</td>';
					
					// API Used
					$api_used = get_post_meta( $post->ID, 'cfgeo-api-used', true );
					$html .= '<td class="api_key_used column-api_key_used">' . esc_html( $api_used ) . '</td>';
					
					// Date
					$html .= '<td class="date column-date">' . get_the_date( '', $post->ID ) . '</td>';
					
					$html .= '</tr>';
				}
			} else {
				$html = '<tr><td colspan="8" style="text-align: center; padding: 20px;">' . esc_html__( 'No submissions found matching your criteria.', 'track-geolocation-of-users-using-contact-form-7' ) . '</td></tr>';
			}

			// Generate pagination
			$pagination = '';
			if ( $total_posts > 20 ) {
				$total_pages = ceil( $total_posts / 20 );
				$current_page = $args['paged'];
				
				$pagination = '<div class="tablenav-pages">';
				$pagination .= '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_posts, 'track-geolocation-of-users-using-contact-form-7' ), number_format_i18n( $total_posts ) ) . '</span>';
				
				if ( $total_pages > 1 ) {
					$pagination .= '<span class="pagination-links">';
					
					// Previous page
					if ( $current_page > 1 ) {
						$pagination .= '<a class="prev-page" href="#" data-page="' . ( $current_page - 1 ) . '">&lsaquo;</a>';
					}
					
					// Page numbers
					$start_page = max( 1, $current_page - 2 );
					$end_page = min( $total_pages, $current_page + 2 );
					
					for ( $i = $start_page; $i <= $end_page; $i++ ) {
						if ( $i == $current_page ) {
							$pagination .= '<span class="paging-input"><span class="tablenav-paging-text">' . $i . '</span></span>';
						} else {
							$pagination .= '<a class="paging-input" href="#" data-page="' . $i . '">' . $i . '</a>';
						}
					}
					
					// Next page
					if ( $current_page < $total_pages ) {
						$pagination .= '<a class="next-page" href="#" data-page="' . ( $current_page + 1 ) . '">&rsaquo;</a>';
					}
					
					$pagination .= '</span>';
				}
				
				$pagination .= '</div>';
			}

			// Debug logging
			error_log('CFGEO AJAX: Sending response with ' . $total_posts . ' posts');
			
			// Send response
			wp_send_json_success( array(
				'html' => $html,
				'pagination' => $pagination,
				'total' => $total_posts
			) );
		}

		/**
		 * AJAX handler for testing webhook
		 *
		 * @method action__cfgeo_ajax_test_webhook
		 */
		function action__cfgeo_ajax_test_webhook() {
			// Verify nonce - check both possible nonce names
			$nonce_valid = false;
			if (isset($_POST['nonce'])) {
				$nonce_valid = wp_verify_nonce($_POST['nonce'], 'cfgeo_filter_nonce') || 
							   wp_verify_nonce($_POST['nonce'], 'cfgeo_webhook_test_nonce');
			}
			
			if (!$nonce_valid) {
				wp_send_json_error(array(
					'message' => __('Security check failed. Please refresh the page and try again.', 'track-geolocation-of-users-using-contact-form-7')
				));
			}

			// Check permissions
			if ( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error(array(
					'message' => __('Insufficient permissions to perform this action.', 'track-geolocation-of-users-using-contact-form-7')
				));
			}

			// Check if webhooks are enabled
			if (!get_option('cfgeo_webhook_enabled')) {
				wp_send_json_error( array(
					'message' => __( 'Webhooks are not enabled. Please enable webhooks first.', 'track-geolocation-of-users-using-contact-form-7' )
				) );
			}

			// Get webhook URLs
			$webhook_urls = get_option('cfgeo_webhook_urls');
			if (empty($webhook_urls)) {
				wp_send_json_error( array(
					'message' => __( 'No webhook URLs configured. Please add webhook URLs first.', 'track-geolocation-of-users-using-contact-form-7' )
				) );
			}

			// Prepare test payload
			$test_payload = array(
				'timestamp' => current_time('c'),
				'site_url' => get_site_url(),
				'form_data' => array(
					'test_field' => 'Test Value',
					'email' => 'test@example.com'
				),
				'geolocation' => array(
					'country' => 'United States',
					'state' => 'California',
					'city' => 'San Francisco',
					'latitude' => '37.7749',
					'longitude' => '-122.4194',
					'lat_long' => '37.7749,-122.4194',
					'ip_address' => '127.0.0.1'
				),
				'user_agent' => 'Test Webhook',
				'ip_address' => '127.0.0.1',
				'test_mode' => true
			);

			// Add webhook secret if configured
			$webhook_secret = get_option('cfgeo_webhook_secret');
			if (!empty($webhook_secret)) {
				$test_payload['signature'] = hash_hmac('sha256', wp_json_encode($test_payload), $webhook_secret);
			}

			// Get timeout setting
			$timeout = get_option('cfgeo_webhook_timeout', 30);
			$timeout = intval($timeout);
			if ($timeout < 5) $timeout = 5;
			if ($timeout > 60) $timeout = 60;

			// Split URLs by line
			$urls = array_filter(array_map('trim', explode("\n", $webhook_urls)));
			$success_count = 0;
			$total_count = count($urls);

			foreach ($urls as $url) {
				if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
					continue;
				}

				$args = array(
					'body' => wp_json_encode($test_payload),
					'timeout' => $timeout,
					'headers' => array(
						'Content-Type' => 'application/json',
						'User-Agent' => 'CF7-Geolocation-Webhook/1.0'
					)
				);

				$response = wp_remote_post($url, $args);
				$success = !is_wp_error($response) && wp_remote_retrieve_response_code($response) >= 200 && wp_remote_retrieve_response_code($response) < 300;

				if ($success) {
					$success_count++;
				}
			}

			if ($success_count > 0) {
				wp_send_json_success( array(
					'message' => sprintf(
						__( 'Test completed successfully! %d out of %d webhooks responded successfully.', 'track-geolocation-of-users-using-contact-form-7' ),
						$success_count,
						$total_count
					)
				) );
			} else {
				wp_send_json_error( array(
					'message' => __( 'Test failed. None of the configured webhooks responded successfully. Please check your webhook URLs and try again.', 'track-geolocation-of-users-using-contact-form-7' )
				) );
			}
		}

		/**
		 * Manual test function for webhook functionality
		 *
		 * @method cfgeo_test_webhook_manual
		 */
		function cfgeo_test_webhook_manual() {
			if (!current_user_can('manage_options')) {
				return;
			}
			
			$test_form_data = array(
				'name' => 'Test User',
				'email' => 'test@example.com',
				'message' => 'This is a test webhook submission'
			);
			
			$test_geo_data = array(
				'country' => 'United States',
				'state' => 'California',
				'city' => 'San Francisco',
				'latitude' => '37.7749',
				'longitude' => '-122.4194',
				'lat_long' => '37.7749,-122.4194',
				'ip_address' => '127.0.0.1'
			);
			
			// Call the webhook function from the lib class
			CFGEO()->lib->cfgeo_send_webhook_data($test_form_data, $test_geo_data);
		}

		/**
		 * AJAX handler for getting webhook logs
		 *
		 * @method action__cfgeo_ajax_get_webhook_logs
		 */
		function action__cfgeo_ajax_get_webhook_logs() {
			// Verify nonce - check both possible nonce names
			$nonce_valid = false;
			if (isset($_POST['nonce'])) {
				$nonce_valid = wp_verify_nonce($_POST['nonce'], 'cfgeo_filter_nonce') || 
							   wp_verify_nonce($_POST['nonce'], 'cfgeo_webhook_logs_nonce');
			}
			
			if (!$nonce_valid) {
				wp_send_json_error(array(
					'message' => __('Security check failed. Please refresh the page and try again.', 'track-geolocation-of-users-using-contact-form-7')
				));
			}

			// Check permissions
			if ( !current_user_can( 'manage_options' ) ) {
				wp_send_json_error(array(
					'message' => __('Insufficient permissions to perform this action.', 'track-geolocation-of-users-using-contact-form-7')
				));
			}

			$logs = get_option('cfgeo_webhook_logs', array());
			
			// Format timestamps for display
			foreach ($logs as &$log) {
				$timestamp = strtotime($log['timestamp']);
				$log['timestamp'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : $log['timestamp'];
			}

			wp_send_json_success( array(
				'logs' => $logs
			) );
		}
	}

	add_action( 'plugins_loaded', function() {
		cfgeo()->admin->action = new CFGEO_Admin_Action;
	} );
}
