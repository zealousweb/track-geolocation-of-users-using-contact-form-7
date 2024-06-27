<?php
/**
 * cfgeo_Lib Class
 *
 * Handles the Library functionality.
 *
 * @package WordPress
 * @subpackage Track Geolocation Of Users Using Contact Form 7
 * @since 2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'cfgeo_Lib' ) ) {

	class cfgeo_Lib {

		private $lib_version = '1.0.0'; // lib github commit
		var $context = '';

		static  $activation_menuname    = 'Geolocation Settings',
				$setting_page           = 'geolocation-setting',
				$google_api_link        = 'https://developers.google.com/maps/documentation/geolocation/get-api-key';

		function __construct() {

			add_action( 'admin_init',               array( $this, 'cfgeo_display_options'));
			add_action( 'admin_menu',               array( $this, 'zw_settings_menu' ) );
			add_action( 'wpcf7_before_send_mail',   array( $this, 'cfgeo_before_send_mail' ), 20, 3 );

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
		 * Action: admin menu
		 *
		 * - Create Admin Menu.
		 *
		 * @method admin_menu
		 *
		 */
		function zw_settings_menu() {
			add_submenu_page(
				'wpcf7',
				self::$activation_menuname,
				self::$activation_menuname,
				'manage_options',
				self::$setting_page,
				array( $this, 'cfgeo_setting_page' )
			);
		}

		/**
		 * [cfgeo_display_options Add & Register field for settings page.]
		*/
		function cfgeo_display_options(){
			if ( get_option( 'cfgeo_debug_mode' ) === false ){ // Nothing yet saved
				update_option( 'cfgeo_debug_mode', 1 );
			}
			if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' ))
			{
				//Add a new section to a settings page.
				add_settings_section("cfgeo_googleapi", "", array( $this, 'cfgeo_display_header_content'), self::$setting_page);
				if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' ))
				{
					//Add a new section to a settings page.
					add_settings_section("cfgeo_googleapi", "", array( $this, 'cfgeo_display_header_content'), self::$setting_page);
					//Add a new field to a section of a settings page.
					add_settings_field("cfgeo_debug_mode",     __("<label>Enable Debug Mode </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-debug></span>", 'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_debug_data'), self::$setting_page, "cfgeo_googleapi");
					add_settings_field("cfgeo_color_picker",   __("<label>Select Color of the Graph </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-color-graph></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_color_picker'));
					add_settings_field("cfgeo_google_api_key", __("<label>Google Map API Key </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-google></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_google_api_key'));
					add_settings_field("cfgeo_ipstack_access", __("<label>Access Token For IPstack </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-ipstack></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_ipstack_access'));

					//Registers a setting and its data.
					register_setting("cfgeo_googleapi", "cfgeo_debug_mode");
					register_setting("cfgeo_googleapi", "cfgeo_google_api_key");
					register_setting("cfgeo_googleapi", "cfgeo_ipstack_access");
				}
			}else{
					//Add a new section to a settings page.
					add_settings_section("cfgeo_googleapi", "", array( $this, 'cfgeo_display_header_content'), self::$setting_page);
					//Add a new field to a section of a settings page.
					add_settings_field("cfgeo_debug_mode",     __("<label>Enable Debug Mode </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-debug></span>", 'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_debug_data'), self::$setting_page, "cfgeo_googleapi");
					add_settings_field("cfgeo_color_picker",   __("<label>Select Color of the Graph </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-color-graph></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_color_picker'));
					add_settings_field("cfgeo_google_api_key", __("<label>Google Map API Key </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-google></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_google_api_key'));
					add_settings_field("cfgeo_ipstack_access", __("<label>Access Token For IPstack </label><span class=cfgeo-tooltip hide-if-no-js id=cfgeo-ipstack></span>",'track-geolocation-of-users-using-contact-form-7'), array( $this, 'cfgeo_display_setting_field_data'), self::$setting_page, "cfgeo_googleapi", array('cfgeo_ipstack_access'));

					//Registers a setting and its data.
					register_setting("cfgeo_googleapi", "cfgeo_debug_mode");
					register_setting("cfgeo_googleapi", "cfgeo_color_picker");
					register_setting("cfgeo_googleapi", "cfgeo_google_api_key");
					register_setting("cfgeo_googleapi", "cfgeo_ipstack_access");
			}
		}

		/**
		 * Action: CF7 before send email
		 *
		 * @method cfgeo_before_send_mail
		 *
		 * @param  object $contact_form WPCF7_ContactForm::get_instance()
		 * @param  bool   $abort
		 * @param  object $contact_form WPCF7_Submission class
		 *
		 */
		function cfgeo_before_send_mail( $contact_form, $abort, $wpcf7_submission ) {

			require_once(ABSPATH . 'wp-admin/includes/file.php');
			$upload_dir    = wp_upload_dir();
			$cfgeo_dirname = $upload_dir['basedir'].'/cfgeodb_uploads';
			if(!file_exists($cfgeo_dirname)) wp_mkdir_p($cfgeo_dirname);
			$time_now      = time();

			$submission = WPCF7_Submission::get_instance();
			$form_id = $contact_form->id();
			$form_instance = WPCF7_ContactForm::get_instance($form_id);
			$ini_post_id = $this->cfgeo_insert_post_title($submission);
			if ( $submission ) {

				$black_list     = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag',
										'_wpcf7_is_ajax_call','_wpcf7_container_post','_wpcf7cf_hidden_group_fields',
										'_wpcf7cf_hidden_groups', '_wpcf7cf_visible_groups', '_wpcf7cf_options','g-recaptcha-response');

				$data           = $submission->get_posted_data();
				$files          = $submission->uploaded_files();
				$uploaded_files = array();
				$rm_underscore  = apply_filters('cfgeodb_remove_underscore_data', true);
				$form_data      = array();

				foreach ($files as $file_key => $file) {
					array_push($uploaded_files, $file_key);
				}

				$form_data['_form_id'] = $form_id;
				$form_date    = current_time('Y-m-d H:i:s');
				$title_count  = 0;
				$root_path = get_home_path();
				foreach ($data as $key => $d) {
					$matches = array();
					if( $rm_underscore ) preg_match('/^_.*$/m', $key, $matches);

					if ( !in_array($key, $black_list ) && !in_array($key, $uploaded_files ) && empty( $matches[0] ) ) {

						$tmpD = $d;

						if ( ! is_array($d) ){
							$bl   = array('\"',"\'",'/','\\','"',"'");
							$wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');
							$tmpD = str_replace($bl, $wl, $tmpD );
						}
						$form_data[$key] = $tmpD;
						add_post_meta( $ini_post_id, sanitize_text_field($key), sanitize_textarea_field($d));
					}
					if ( in_array($key, $uploaded_files ) ) {

						$cf7_verify = $this->wpcf7_version();
						if ( version_compare( $cf7_verify, '5.4' ) >= 0 ) {
							$upload_files = $this->cfgeo_upload_files( $files, 'new' );
						}else{
							$upload_files = $this->cfgeo_upload_files( array( $files ), 'old' );
						}

						foreach ($upload_files as $key => $final_path) {
							$final_attachment_url = home_url().str_replace( $root_path, '/', $final_path );
							$form_data[$key] = sanitize_text_field($final_attachment_url);
							add_post_meta( $ini_post_id, $key, $final_attachment_url);
						}
					}
				}
				// get IP
				$ip = $this->cfgeo_get_ip();

				// Get Location based on IP
				$get_loc = $this->cfgeo_get_location($ip, $ini_post_id);
				if($get_loc != ''){
					$lat_long                       = ($get_loc['latitude'] && $get_loc['longitude'])? $get_loc['latitude'].','.$get_loc['longitude'] : '';
					$form_data['cfgeo-country']     = ($get_loc['country']) ? $get_loc['country'] : '';
					$form_data['cfgeo-state']       = ($get_loc['region']) ? $get_loc['region'] : '';
					$form_data['cfgeo-city']        = ($get_loc['city']) ? $get_loc['city'] : '';
					$form_data['cfgeo-lat-long']    = $lat_long;

					add_post_meta( $ini_post_id, 'cfgeo-country', $form_data['cfgeo-country'], $unique = false );
					add_post_meta( $ini_post_id, 'cfgeo-state', $form_data['cfgeo-state'], $unique = false );
					add_post_meta( $ini_post_id, 'cfgeo-city', $form_data['cfgeo-city'], $unique = false );
					add_post_meta( $ini_post_id, 'cfgeo-lat-long', $lat_long, $unique = false );
				}
				$form_value   = serialize( $form_data );
				add_post_meta( $ini_post_id, '_form_id', $form_id);
				add_post_meta( $ini_post_id, '_form_data', $form_value );
			}
			add_filter( 'wpcf7_skip_mail', array( $this, 'cfgeo_filter__wpcf7_skip_mail' ), 20 );
			$this->mail( $form_instance, $data ,$form_data);
		}

		/**
		 * Email send
		 *
		 * @method mail
		 *
		 * @param  object $contact_form WPCF7_ContactForm::get_instance()
		 * @param  [type] $posted_data  WPCF7_Submission::get_posted_data()
		 *
		 * @uses $this->prop(), $this->mail_replace_tags(), $this->get_form_attachments(),
		 *
		 * @return bool
		 */
		function mail( $contact_form, $posted_data, $geolocation_data) {
			if( empty( $contact_form ) ) {
				return false;
			}
			$contact_form_data = $contact_form;
			$mail = $contact_form_data->prop( 'mail' );
			$use_html = $mail['use_html'];
			$mail = $this->mail_replace_tags( $mail, $posted_data, $geolocation_data, $use_html );
			$result = WPCF7_Mail::send( $mail, 'mail' );

			if ( $result ) {
				$additional_mail = array();

				if (
					$mail_2 = $this->prop( 'mail_2', $contact_form_data )
					and $mail_2['active']
				) {
					$use_html_2 = $mail_2['use_html'];
					$mail_2 = $this->mail_replace_tags( $mail_2, $posted_data, $geolocation_data, $use_html_2 );
					$additional_mail['mail_2'] = $mail_2;
				}

				$additional_mail = apply_filters( 'wpcf7_additional_mail',
					$additional_mail, $contact_form_data );

				foreach ( $additional_mail as $name => $template ) {
					WPCF7_Mail::send( $template, $name );
				}

				return true;
			}

			return false;
		}

		/**
		 * get the property from the
		 *
		 * @method prop    used from WPCF7_ContactForm:prop()
		 *
		 * @param  string $name
		 * @param  object $class_object WPCF7_ContactForm:get_current()
		 *
		 * @return mixed
		 */
		public function prop( $name, $class_object ) {
			$props = $class_object->get_properties();
			return isset( $props[$name] ) ? $props[$name] : null;
		}

		/**
		 * [mail_replace_tags Mail tag replace]
		 * @param  [type] $mail             [description]
		 * @param  [type] $data             [data]
		 * @param  [type] $geolocation_data [Geolocation Data]
		 * @param  [type] $use_html         [usehtml mail option]
		 */
		function mail_replace_tags( $mail, $data, $geolocation_data, $use_html ) {
			$mail = ( array ) $mail;
			$data = ( array ) $data;
			$new_mail = array();
			if ( !empty( $mail ) && !empty( $data ) ) {
				foreach ( $mail as $key => $value ) {
					if( $key != 'attachments' ) {

						foreach ( $data as $k => $v ) {
							if(is_array($v)){
								$v = implode(', ', $v);
							}
							$value = str_replace( '[' . $k . ']' , $v, $value );
						}
						if ( $key == 'body' ){
							if (strpos($value, '[geolocation') !== false) {
								preg_match_all("/\[[^\]]*\]/", $value, $get_shortcode);
								$get_shortcodearray = $this->cfgeo_single_array($get_shortcode);
								foreach($get_shortcodearray as $find_key=>$get){
									if("[geolocation" == substr($get,0,12)){
										$number[] = substr($find_key,strrpos($find_key,'_'));
									}
								}
								foreach ( $get_shortcodearray as $single ) {
									$get_shortcode_att = explode(" ", str_replace(array('[',']'), "", $single));
									$total_att = count($get_shortcode_att);
									$new_data = array();
									foreach ($get_shortcode_att as $att) {

										if($total_att == 1 && $att == 'geolocation' && $geolocation_data != ''){
											$new_data[] = ($geolocation_data['cfgeo-lat-long'])? 'Latitude/Longitude: ' . $geolocation_data['cfgeo-lat-long'] : 'Latitude/Longitude: ';
											$new_data[] = ($geolocation_data['cfgeo-country']) ? 'Country: ' .$geolocation_data['cfgeo-country'] : 'Country: ';
											$new_data[] = ($geolocation_data['cfgeo-state']) ? 'State: '.$geolocation_data['cfgeo-state'] : 'State: ';
											$new_data[] = ($geolocation_data['cfgeo-city']) ? 'City: '. $geolocation_data['cfgeo-city'] : 'City: ';
										}

										if($total_att > 1 && $att == 'lat-long' && $geolocation_data != ''){
											$new_data[] = ($geolocation_data['cfgeo-lat-long'])? 'Latitude/Longitude: ' . $geolocation_data['cfgeo-lat-long'] : 'Latitude/Longitude: ';
										}

										if($total_att > 1 && $att == 'city' && $geolocation_data != ''){
											$new_data[] = ($geolocation_data['cfgeo-city']) ? 'City: '. $geolocation_data['cfgeo-city'] : 'City: ';
										}

										if($total_att > 1 && $att == 'state' && $geolocation_data != ''){
											$new_data[] = ($geolocation_data['cfgeo-state']) ? 'State: '.$geolocation_data['cfgeo-state'] : 'State: ';
										}

										if($total_att > 1 && $att == 'country' && $geolocation_data != ''){
											$new_data[] = ($geolocation_data['cfgeo-country']) ? 'Country: ' .$geolocation_data['cfgeo-country'] : 'Country: ';
										}

										if($total_att > 1 && $att == 'gmap' && $use_html == 1 && $geolocation_data != ''){
											$lat_long_sep = explode(",",$geolocation_data['cfgeo-lat-long']);
											if(count($lat_long_sep) > 1){
												$map = add_query_arg(
													array(
														'q'  => $geolocation_data['cfgeo-city'] . ',' . $geolocation_data['cfgeo-state'],
														'll' => $lat_long_sep[0] . ',' . $lat_long_sep[1],
														'z'  => 6,
													),
													'https://maps.google.com/maps'
												);
												$img = add_query_arg(
													array(
														'center'  => $geolocation_data['cfgeo-city'] . ',' . $geolocation_data['cfgeo-state'],
														'll'      => $lat_long_sep[0] . ',' . $lat_long_sep[1],
														'size'    => '300x100',
														'maptype' => 'roadmap',
														'zoom'    => 6,
														'markers' => 'color:red%7C' . $lat_long_sep[0] . ',' . $lat_long_sep[1],
														'key'	  => get_option('cfgeo_google_api_key'),
													),
													'https://maps.googleapis.com/maps/api/staticmap'
												);
												$new_data[] = '<br><a href="' . esc_url( $map ) . '" rel="noopener noreferrer" target="_blank"><img src="' . esc_url_raw( $img ) . '"></a>';
											}
										}
									}
									$geo_data[str_replace(array('[',']'), "",$single)] = implode( "\n", $new_data );
								}
								foreach ($geo_data as $replace => $data_key) {
									if("geolocation" == substr($replace,0,11)){
										$value = str_replace('['.$replace.']', $data_key, $value);
									}
								}
							}
						}
					}
					$new_mail[ $key ] = $value;
				}
			}
			return $new_mail;
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
		 * [cfgeo_filter__wpcf7_skip_mail Skip Mail]
		 * @param  [type] $bool [description]
		 * @return [type]       [description]
		 */
		function cfgeo_filter__wpcf7_skip_mail( $bool ) {
			return true;
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
		 * [cfgeo_setting_page Include Setting Page Template]]
		*/
		function cfgeo_setting_page(){

			require_once( CFGEO_DIR .  '/inc/admin/template/' . CFGEO_PREFIX . '.template.php' );

		}

		/**
		 * [cfgeo_display_header_content Add Header content in setting page.]
		 * @return [html] [message]
		 */
		function cfgeo_display_header_content(){
			if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
				if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
					echo '<br>You can get your Google Map API key from <a href="' . esc_url( self::$google_api_link ) . '" target="_blank">' . esc_html__( 'here', 'track-geolocation-of-users-using-contact-form-7' ) . '</a>';
				}
			}else{
				echo '<br>You can get your Google Map API key from <a href="' . esc_url( self::$google_api_link ) . '" target="_blank">' . esc_html__( 'here', 'text-domain' ) . '</a>';
			}
		}

		/**
		 * [cfgeo_display_debug_data Display Debug field Checkbox in setting page.]
		 * @return [html] [field generate]
		 */
		function cfgeo_display_debug_data()
		{
			//id and name of form element should be same as the setting name.
			?>
			<input type="checkbox" name="cfgeo_debug_mode" id="cfgeo_debug_mode" value="1" <?php checked( 1, get_option('cfgeo_debug_mode'), true ); ?> />
			<?php
		}

		/**
		 * [cfgeo_display_setting_field_data Display Setting field in setting page.]
		 * @param  [array] $args [field name,id,class]
		 * @return [html]       [field]
		 */
		function cfgeo_display_setting_field_data($args)
		{
			$option = get_option($args[0]);
			//id and name of form element should be same as the setting name.
			echo '<input type="text" name="' . esc_attr( $args[0] ) . '" id="' . esc_attr( $args[0] ) . '" value="' . esc_attr( $option ) . '" class="' . esc_attr( $args[0] ) . '" size="50" />';
		}

		/**
		 * [cfgeo_get_meta_values Get entry total on the basis of country to display in graph chart.]
		 * @param  string $key       [country meta key]
		 * @param  string $type      [post type]
		 * @param  string $status    [post status]
		 * @param  string $form_meta [form id]
		 * @return [array]            [country and its entry count]
		 */
		function cfgeo_get_meta_values( $key = '', $type = CFGEO_POST_TYPE, $status = 'publish', $form_meta = '_form_id' ) {
			global $wpdb;
			$selected = ( isset( $_GET['form-id']) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )) ? sanitize_text_field($_GET['form-id']) : '' ;

			if( empty( $key ) )
				return;
			$metas = array();
			$final_country_cnt = array();
			$get_country_post = $wpdb->get_results( $wpdb->prepare( "
				SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
				AND p.post_status = %s
				AND p.post_type = %s
			", $key, $status, $type ));
			foreach ( $get_country_post as $get_country_data ){
				if($get_country_data->meta_value != ''){
					if($selected == '' || $selected == 'all'){
						$metas[] = $get_country_data->meta_value;
					}else{
						if(get_post_meta( $get_country_data->ID, $form_meta, true) == $selected){
							$metas[] = $get_country_data->meta_value;
						}
					}
				}
			}
			// $final_country_cnt[] = "['".$key."' ,".$value."]";
			if($metas){
				$cnt_entry = array_count_values($metas);
				foreach ($cnt_entry as $key => $value) {
					$new = array();
					$new['ctrname'] = $key;
					$new['etr'] = $value;
					$final_country_cnt[] = $new;
				}
			}
			return wp_json_encode($final_country_cnt);
		}

		/**
		 * [cfgeo_insert_post_title Insert post]
		 * @param  [array] $form [Form data]
		 * @return [int]       [postid]
		 */
		function cfgeo_insert_post_title($form){

			$data = $form->get_posted_data();
			$current_form_id = WPCF7_ContactForm::get_current();

			$contactform = WPCF7_ContactForm::get_instance( $current_form_id->id() );
			$form_fields = $contactform->scan_form_tags();

			$title_count = 0;
			foreach ($form_fields as $key) {
				if($key['basetype'] == 'email' && $title_count == 0){
					$title = $key['name'];
					$title_count = 1;
				}
			}
			$final_post_title = $data[$title];
			$geo_post_id = wp_insert_post( array (
								'post_type'      => CFGEO_POST_TYPE,
								'post_title'     => $final_post_title, // email
								'post_status'    => 'publish',
								'comment_status' => 'closed',
								'ping_status'    => 'closed',
							) );
			return $geo_post_id;
		}
		/**
		 * [cfgeo_single_array converting into single array]
		 * @param  [array] $array [multi]
		 * @return [array]        [description]
		 */
		function cfgeo_single_array($array){
			foreach($array as $arr)
			{
				foreach($arr as $val)
				{
					$new_array[] = $val;
				}
			}
			return $new_array;
		}

		/**
		 * [cfgeo_get_ip Get User IP]
		 * @return [string] [returns IP]
		 */
		function cfgeo_get_ip() {
			$ip = false;

			if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
				$ip = filter_var( $_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP );
			} elseif ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				// Check ip from share internet.
				$ip = filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP );
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
				if ( is_array( $ips ) ) {
					$ip = filter_var( $ips[0], FILTER_VALIDATE_IP );
				}
			} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
			}

			$ip       = false !== $ip ? $ip : '127.0.0.1';
			$ip_array = explode( ',', $ip );
			$ip_array = array_map( 'trim', $ip_array );
			if($ip_array[0] == '::1' || $ip_array[0] == '127.0.0.1'){
				$ipser = array('http://ipv4.icanhazip.com','http://v4.ident.me','http://bot.whatismyipaddress.com');
				shuffle($ipser);
				$ipservices = array_slice($ipser, 0,1);
				$ret = wp_remote_get($ipservices[0]);
				if(!is_wp_error($ret)){
					if (isset($ret['body'])) {
						return sanitize_text_field( $ret['body'] );
					}
				}
			}

			return sanitize_text_field( apply_filters( 'cfgeo_get_ip', $ip_array[0] ) );
		}

		/**
		 * [cfgeo_get_location Get User Location based on IP]
		 * @param  [string] $ip     [User IP]
		 * @param  [int] $postid [Postid]
		 * @return [array]         [Geolocation Details]
		 */
		function cfgeo_get_location( $ip, $postid ){
			$ipstack_access = get_option('cfgeo_ipstack_access');
			if($ipstack_access != ''){
				$request = $this->cfgeo_ipstack($ip, $postid);
				if($request['latitude'] == ''){
					$request = $this->cfgeo_ipapi($ip, $postid);
					if($request['latitude'] == ''){
						$request = $this->cfgeo_keycdn($ip, $postid);
					}
				}
				return $request;
			}else{
				$request = $this->cfgeo_ipapi($ip, $postid);
				if($request['latitude'] == ''){
					$request = $this->cfgeo_keycdn($ip, $postid);
				}
				return $request;
			}
		}

		/**
		 * [cfgeo_ipstack Get User Location details through ipstack api.]
		 * @param  [string] $ip     [User IP]
		 * @param  [int] $postid [postid]
		 * @return [array]         [Geolocation Details]
		 */
		function cfgeo_ipstack($ip, $postid){
			$request = wp_remote_get( 'http://api.ipstack.com/'. $ip .'?access_key='.get_option('cfgeo_ipstack_access').'' );
			$data = array(
				'latitude'  => '',
				'longitude' => '',
				'city'      => '',
				'region'    => '',
				'country'   => '',
				'postal'    => '',
			);
			if ( !is_wp_error( $request ) ) {

				$request = json_decode( $request['body'] );
				if(isset($request->error) && $request->error != '' && get_option( 'cfgeo_debug_mode' ) == 1){
					add_post_meta( $postid, 'cfgeo-debug-ipstack', $request->error->info );
				}elseif(!isset($request->error)){
					$data = array(
						'latitude'  => sanitize_text_field( isset($request->latitude) ? $request->latitude : '' ),
						'longitude' => sanitize_text_field( isset($request->longitude) ? $request->longitude : '' ),
						'city'      => sanitize_text_field( isset($request->city) ? $request->city : '' ),
						'region'    => sanitize_text_field( isset($request->region_name) ? $request->region_name : '' ),
						'country'   => sanitize_text_field( isset($request->country_name) ? $request->country_name : '' ),
						'postal'    => sanitize_text_field( isset($request->zip) ? $request->zip : '' ),
					);
					update_post_meta( $postid, 'cfgeo-api-used', 'ipstack' );
				}
				if((isset($request->error) && $request->error->info != '') || $data['latitude'] == ''){
					$debug_log_mess = $postid.' - '.$request->error->info.' - '.$ip.' ipstack';
					$ipstack_log = $this->cfgeo_custom_logs($debug_log_mess);
				}
			}

			return $data;
		}

		/**
		 * [cfgeo_ipapi Get User Location details through ipapi api.]
		 * @param  [string] $ip     [User IP]
		 * @param  [int] $postid [postid]
		 * @return [array]         [Geolocation Details]
		 */
		function cfgeo_ipapi($ip, $postid){
			$request = wp_remote_get( 'https://ipapi.co/' . $ip . '/json' );
			$data = array(
				'latitude'  => '',
				'longitude' => '',
				'city'      => '',
				'region'    => '',
				'country'   => '',
				'postal'    => '',
			);
			if ( ! is_wp_error( $request ) ) {

				$request = json_decode( wp_remote_retrieve_body( $request ), true );

				if(isset($request['reason']) && $request['reason'] != '' && get_option( 'cfgeo_debug_mode' ) == 1){
					add_post_meta( $postid, 'cfgeo-debug-ipapi', $request['reason'] );
				}elseif (!isset($request['reason'])) {
					$data = array(
						'latitude'  => sanitize_text_field( isset($request['latitude']) ? $request['latitude'] : '' ),
						'longitude' => sanitize_text_field( isset($request['longitude']) ? $request['longitude'] : '' ),
						'city'      => sanitize_text_field( isset($request['city']) ? $request['city'] : '' ),
						'region'    => sanitize_text_field( isset($request['region']) ? $request['region'] : '' ),
						'country'   => sanitize_text_field( isset($request['country_name']) ? $request['country_name'] : '' ),
						'postal'    => sanitize_text_field( isset($request['postal']) ? $request['postal'] : '' ),
					);
					update_post_meta( $postid, 'cfgeo-api-used', 'ipapi' );
				}
				if((isset($request['reason']) && $request['reason'] != '') || $data['latitude'] == ''){
					$debug_log_mess = $postid.' - '.$request['reason'].' - '.$ip.' ipapi';
					$ipapi_log = $this->cfgeo_custom_logs($debug_log_mess);
				}
			}
			return $data;
		}

		/**
		 * [cfgeo_keycdn Get User Location details through keycdn api.]
		 * @param  [string] $ip     [User IP]
		 * @param  [int] $postid [postid]
		 * @return [array]         [Geolocation Details]
		 */
		function cfgeo_keycdn($ip, $postid){
			$request = wp_remote_get( 'https://tools.keycdn.com/geo.json?host=' . $ip );
			$data = array(
				'latitude'  => '',
				'longitude' => '',
				'city'      => '',
				'region'    => '',
				'country'   => '',
				'postal'    => '',
			);
			if ( !is_wp_error( $request ) ) {

				$request = json_decode( $request['body'] );

				if(isset($request->status) && $request->status == 'error' && get_option( 'cfgeo_debug_mode' ) == 1){
					add_post_meta( $postid, 'cfgeo-debug-keycdn', $request->description );
				}elseif($request->status != 'error'){
					$data = array(
						'latitude'  => sanitize_text_field( isset($request->data->geo->latitude) ? $request->data->geo->latitude : '' ),
						'longitude' => sanitize_text_field( isset($request->data->geo->longitude) ? $request->data->geo->longitude : '' ),
						'city'      => sanitize_text_field( isset($request->data->geo->city) ? $request->data->geo->city : '' ),
						'region'    => sanitize_text_field( isset($request->data->geo->region_name) ? $request->data->geo->region_name : '' ),
						'country'   => sanitize_text_field( isset($request->data->geo->country_name) ? $request->data->geo->country_name : '' ),
						'postal'    => sanitize_text_field( isset($request->data->geo->postal_code) ? $request->data->geo->postal_code : '' ),
					);
					update_post_meta( $postid, 'cfgeo-api-used', 'keycdn' );
				}
				if((isset($request->status) && $request->status == 'error') || $data['latitude'] == ''){
					$debug_log_mess = $postid.' - '.$request->description.' - '.$ip.' keycdn';
					$keycdn_log = $this->cfgeo_custom_logs($debug_log_mess);
				}
			}
			return $data;
		}

		/**
		* Get the attachment upload directory from plugin.
		*
		* @method cfgeo_upload_tmp_dir
		*
		* @return string
		*/
		function cfgeo_upload_tmp_dir() {

			$upload = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$cfgeo_upload_dir = $upload_dir . '/cfgeodb_uploads';

			if ( !is_dir( $cfgeo_upload_dir ) ) {
				return $cfgeo_upload_dir;
			}

		}

		/**
		 * Copy the attachment into the plugin folder.
		 *
		 * @method cfgeo_upload_files
		 *
		 * @param  array $attachment
		 *
		 * @uses $this->cfgeo_upload_tmp_dir(), WPCF7::wpcf7_maybe_add_random_dir()
		 *
		 * @return array
		 */
		function cfgeo_upload_files( $attachment, $version ) {
			if ( empty( $attachment ) ) {
				return;
			}
		
			// Initialize WP_Filesystem
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		
			global $wp_filesystem;
		
			if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
				// Filesystem initialization failed, handle error
				return;
			}
		
			$new_attachment_file = array();
		
			foreach ( $attachment as $key => $value ) {
				$tmp_name = $value;
				$uploads_dir = wpcf7_maybe_add_random_dir( $this->cfgeo_upload_tmp_dir() );
				
				foreach ( $tmp_name as $newkey => $file_path ) {
					$get_file_name = explode( '/', $file_path );
					$new_uploaded_file = path_join( $uploads_dir, end( $get_file_name ) );
		
					if ( $wp_filesystem->copy( $file_path, $new_uploaded_file, true ) ) {
						$wp_filesystem->chmod( $new_uploaded_file, 0755 );
		
						if ( $version == 'old' ) {
							$new_attachment_file[ $newkey ] = $new_uploaded_file;
						} else {
							$new_attachment_file[ $key ] = $new_uploaded_file;
						}
					}
				}
			}
		
			return $new_attachment_file;
		}
		

		/**
		 * [cfgeo_custom_logs Custom Log.]
		 * @param  [string] $message [Error Log Message]
		 * @return [string]          [description]
		 */
		function cfgeo_custom_logs($message) {
			//log format: postid - error message - API name
			if(is_array($message)) {
				$message = wp_json_encode($message);
			}
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		
			global $wp_filesystem;
		
			if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
				// Filesystem initialization failed, handle error
				return;
			}
		
			// Path to the log file
			$log_file = trailingslashit( WP_CONTENT_DIR ) . 'cf7-geo.log';
		
			// Append to the log file
			$current_time = gmdate('Y-m-d h:i:s');
			$log_content = "\n" . $current_time . " :: " . $message;
		
			if ( ! $wp_filesystem->exists( $log_file ) ) {
				$wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE );
			}
		
			$wp_filesystem->append_to_file( $log_file, $log_content );
		}

		/**
		 * Get current conatct from 7 version.
		 *
		 * @method wpcf7_version
		 *
		 * @return string
		 */
		function wpcf7_version() {

			$wpcf7_path = plugin_dir_path( CFGEO_DIR ) . 'contact-form-7/wp-contact-form-7.php';

			if( ! function_exists('get_plugin_data') ){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( $wpcf7_path );

			return $plugin_data['Version'];
		}

	}
	add_action( 'plugins_loaded', function() {
		CFGEO()->lib = new cfgeo_Lib;
	} );
}
