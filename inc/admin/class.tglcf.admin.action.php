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
			
			// Add inline CSS for advanced filters
			add_action( 'admin_head', array( $this, 'action__cfgeo_admin_head' ) );
			
			// Add JavaScript for enhanced filtering
			add_action( 'admin_footer', array( $this, 'action__cfgeo_admin_footer' ) );
		}

		/**
		 * Action: admin_head
		 *
		 * - Add inline CSS for advanced filters
		 *
		 */
		function action__cfgeo_admin_head() {
			$screen = get_current_screen();
			if ( $screen && $screen->post_type === CFGEO_POST_TYPE ) {
				?>
				<style type="text/css">
					.cfgeo-advanced-filters {
						margin: 10px 0;
						padding: 15px;
						background: #f9f9f9;
						border: 1px solid #ddd;
						border-radius: 4px;
						box-shadow: 0 1px 3px rgba(0,0,0,0.1);
					}
					.cfgeo-advanced-filters label {
						display: inline-block;
						width: 80px;
						font-weight: bold;
						color: #23282d;
					}
					.cfgeo-advanced-filters input[type="text"],
					.cfgeo-advanced-filters input[type="date"],
					.cfgeo-advanced-filters select {
						border: 1px solid #ddd;
						border-radius: 3px;
						padding: 5px 8px;
						font-size: 13px;
					}
					.cfgeo-advanced-filters input[type="text"]:focus,
					.cfgeo-advanced-filters input[type="date"]:focus,
					.cfgeo-advanced-filters select:focus {
						border-color: #0073aa;
						box-shadow: 0 0 0 1px #0073aa;
						outline: none;
					}
					.cfgeo-advanced-filters .button {
						margin-right: 10px;
					}
					.cfgeo-advanced-filters .button:last-child {
						margin-right: 0;
					}
					.cfgeo-filter-row {
						margin-bottom: 10px;
						display: flex;
						align-items: center;
					}
					.cfgeo-filter-row:last-child {
						margin-bottom: 0;
					}
					.cfgeo-filter-buttons {
						margin-top: 15px;
						padding-top: 15px;
						border-top: 1px solid #ddd;
					}
					.cfgeo-filter-count {
						margin-top: 10px;
						padding: 8px 12px;
						background: #e7f7ff;
						border: 1px solid #b3d9ff;
						border-radius: 3px;
						font-size: 12px;
						color: #0073aa;
					}
				</style>
				<?php
			}
		}

		/**
		 * Action: admin_footer
		 *
		 * - Add JavaScript for enhanced filtering
		 *
		 */
		function action__cfgeo_admin_footer() {
			$screen = get_current_screen();
			if ( $screen && $screen->post_type === CFGEO_POST_TYPE ) {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
					// Auto-submit form when filters change (except for search and date inputs)
					$('#form-id, #country-filter, #city-filter').on('change', function() {
						$('form#posts-filter').submit();
					});

					// Add filter count display
					var totalPosts = $('.wp-list-table tbody tr').length;
					if (totalPosts > 0) {
						var filterInfo = $('<div class="cfgeo-filter-count">Showing ' + totalPosts + ' submission(s)</div>');
						$('.cfgeo-advanced-filters').append(filterInfo);
					}

					// Clear filters functionality
					$('.cfgeo-advanced-filters .button[href*="clear"]').on('click', function(e) {
						e.preventDefault();
						window.location.href = '<?php echo admin_url( 'edit.php?post_type=' . CFGEO_POST_TYPE ); ?>';
					});

					// Date validation
					$('#date-from, #date-to').on('change', function() {
						var fromDate = $('#date-from').val();
						var toDate = $('#date-to').val();
						
						if (fromDate && toDate && fromDate > toDate) {
							alert('<?php echo esc_js( __( 'From date cannot be later than To date.', 'track-geolocation-of-users-using-contact-form-7' ) ); ?>');
							$(this).val('');
						}
					});

					// Search with Enter key
					$('#search-term').on('keypress', function(e) {
						if (e.which === 13) {
							$('form#posts-filter').submit();
						}
					});
				});
				</script>
				<?php
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
						)
					);
				}

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
			echo '<input type="submit" class="button action" value="' . esc_attr__( 'Filter', 'track-geolocation-of-users-using-contact-form-7' ) . '">';
			echo '<input type="submit" id="export_csv" name="export_csv" class="button action" value="' . esc_attr__( 'Export CSV', 'track-geolocation-of-users-using-contact-form-7' ) . '">';
			echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . CFGEO_POST_TYPE ) ) . '" class="button">' . esc_html__( 'Clear Filters', 'track-geolocation-of-users-using-contact-form-7' ) . '</a>';
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
	}

	add_action( 'plugins_loaded', function() {
		cfgeo()->admin->action = new CFGEO_Admin_Action;
	} );
}
