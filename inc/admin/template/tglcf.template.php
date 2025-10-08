<?php
/**
* Template Used for Settings Page
*
* @package WordPress
* @subpackage Track Geolocation Of Users Using Contact Form 7
* @since 2.0
**/

$cfgeo_form_graph_url    = admin_url("admin.php?page=geolocation-setting&tab=cfgeo-submission-graph&form-id=");
$cfgeo_google_api        = get_option('cfgeo_google_api_key');
$cfgeo_country_cnt       = $this->cfgeo_get_meta_values( 'cfgeo-country' );
$cfgeo_google_api_link   = 'https://developers.google.com/maps/documentation/geolocation/get-api-key';
$cfgeo_graph_color = get_option( 'cfgeo_color_picker' );

if($cfgeo_graph_color == ''){
	// if no color selected this color will be show by default
	$cfgeo_graph_color = '#0073aa';
}
if($cfgeo_country_cnt){
	$cfgeo_data_arr_to_str = $cfgeo_country_cnt;
}else{
	$cfgeo_data_arr_to_str = '';
}
$cfgeo_active_tab = "cfgeo-setting";
if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
	if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
		$cfgeo_active_tab = "cfgeo-setting";
	}elseif($_GET["tab"] == "cfgeo-submission-graph" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
		$cfgeo_active_tab = "cfgeo-submission-graph";
	}elseif($_GET["tab"] == "cfgeo-webhook-api" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
		$cfgeo_active_tab = "cfgeo-webhook-api";
	}else{
		$cfgeo_active_tab = "cfgeo-shortcode-info";
	}
}
?>
<div class="wrap cfgeo-main-layout">
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php echo esc_html__( self::$cfgeo_activation_menuname, 'track-geolocation-of-users-using-contact-form-7' ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-setting' ); ?>" class="nav-tab <?php echo $cfgeo_active_tab == 'cfgeo-setting' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Geolocation Settings', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-submission-graph' ); ?>" class="nav-tab <?php echo esc_attr( $cfgeo_active_tab == 'cfgeo-submission-graph' ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Submission Graph', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-webhook-api' ); ?>" class="nav-tab <?php echo esc_attr( $cfgeo_active_tab == 'cfgeo-webhook-api' ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Webhook API', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-shortcode-info' ); ?>" class="nav-tab <?php echo esc_attr( $cfgeo_active_tab == 'cfgeo-shortcode-info' ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Shortcode Info', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
	</h2>
	<?php settings_errors(); ?>
	<form method="post" action="options.php" class="setting-geolocation">
	<?php
		if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-webhook-api"){
			//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
			settings_fields("cfgeo_webhook_api");
			// all the add_settings_field callbacks is displayed here
			do_settings_sections(self::$cfgeo_setting_page);
			// Add the submit button to serialize the options
			submit_button( 
		   		'Save Changes', 
		    	'cfgeo-submit-btn', 
		    	'submit', 
		    	true, 
			);

		}else{
			//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
			settings_fields("cfgeo_googleapi");
			// all the add_settings_field callbacks is displayed here
			do_settings_sections(self::$cfgeo_setting_page);
			if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
				if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
					// Add the submit button to serialize the options
					submit_button( 
					    'Save Changes', 
					    'cfgeo-submit-btn', 
					    'submit', 
					    true, 
					);
				}
			}else{
				// Add the submit button to serialize the options
				submit_button( 
				    'Save Changes', 
				    'cfgeo-submit-btn', 
				    'submit', 
				    true, 
				);
			}
		}
	?>
	</form>
	
	<?php
	// Webhook API Tab Content (after form for display purposes)
	if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-webhook-api"){
		echo '<div class="cfgeo-webhook-settings">';
		echo '<h3>' . esc_html__( 'Webhook API Configuration', 'track-geolocation-of-users-using-contact-form-7' ) . '</h3>';
		echo '<p>' . esc_html__( 'Configure webhooks to send geolocation data to external platforms when form submissions occur.', 'track-geolocation-of-users-using-contact-form-7' ) . '</p>';
		
		echo '<div class="cfgeo-webhook-info">';
		echo '<h4>' . esc_html__( 'How it works:', 'track-geolocation-of-users-using-contact-form-7' ) . '</h4>';
		echo '<ul>';
		echo '<li>' . esc_html__( 'When a form is submitted, geolocation data will be sent to your configured webhook URL', 'track-geolocation-of-users-using-contact-form-7' ) . '</li>';
		echo '<li>' . esc_html__( 'Data is sent as JSON payload via POST request', 'track-geolocation-of-users-using-contact-form-7' ) . '</li>';
		echo '<li>' . esc_html__( 'You can configure multiple webhooks for different platforms', 'track-geolocation-of-users-using-contact-form-7' ) . '</li>';
		echo '<li>' . esc_html__( 'Webhook delivery is asynchronous and won\'t affect form submission speed', 'track-geolocation-of-users-using-contact-form-7' ) . '</li>';
		echo '</ul>';
		echo '</div>';
		echo '</div>';
	}
	?>
	<?php
	if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-submission-graph" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){ ?>
		<h3><?php esc_html__("A Detailed graph on the basis of submitted forms.",'track-geolocation-of-users-using-contact-form-7'); ?></h3>
		<?php
		$cfgeo_posts = get_posts(
			array(
				'post_type'        => 'wpcf7_contact_form',
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'posts_per_page'   => -1
			)
		);
		if ( empty( $cfgeo_posts ) ) {
			return;
		}
		$cfgeo_selected = ( isset( $_GET['form-id']) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )) ? sanitize_text_field($_GET['form-id']) : '' ;
		echo '<select class="cfgeo-input-text cfgeo-input-select" name="form-id" id="form-id-graph">';
		echo '<option value="all">' . esc_html__( 'All Forms', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
		foreach ( $cfgeo_posts as $cfgeo_post ) {
			echo '<option value="' . esc_attr( $cfgeo_post->ID ) . '" ' . selected( $cfgeo_selected, $cfgeo_post->ID, false ) . '>' . esc_html( $cfgeo_post->post_title ) . '</option>';
		}
		echo '</select>';
		?>
		<div id="entry_submission_graph" style="margin-top:15px; width: 1080px; height: 500px;"></div>
		<?php
		$cfgeo_translation_graph_array = array(
			'google_api'	=> $cfgeo_google_api,
			'graph_color'	=> $cfgeo_graph_color,
			'cntry_data'	=> $cfgeo_data_arr_to_str
		);
		wp_localize_script( CFGEO_PREFIX . '_graph_js', 'translate_string_graph_geo', $cfgeo_translation_graph_array );
		wp_enqueue_script( CFGEO_PREFIX . '_loader_js' );
		wp_enqueue_script( CFGEO_PREFIX . '_graph_js' );
		?>
	<?php
	}
	if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-shortcode-info"){
		echo'
		<table class="shortcode-table">
			<thead>
				<tr>
					<td> <strong>'. esc_html__('Details You get in EMail.','track-geolocation-of-users-using-contact-form-7').'</strong> </td>
					<td> <strong>'. esc_html__('Shortcode','track-geolocation-of-users-using-contact-form-7').'</strong> </td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>'. esc_html__('To add latitude/longitude, country, state, city.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add latitude/longitude, country, state, city & Google map static image.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation lat-long country state city gmap]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just latitude/longitude.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td>
						<input class="cfgeo-input-text" type="text" value="[geolocation lat-long]" style="width: 100%;color: #000;" disabled="">
					</td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just latitude/longitude without label.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td>
						<input class="cfgeo-input-text" type="text" value="[geolocation lat-long label="no"]" style="width: 100%;color: #000;" disabled="">
					</td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just country.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation country]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just Country without label.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation country label="no"]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just state.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation state]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just State without label.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation state label="no"]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just city.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation city]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just City without label.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation city label="no"]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html__('To add just Google map static image.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input class="cfgeo-input-text" type="text" value="[geolocation gmap]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">
						<h3>'. esc_html__('Note: To add Google map static image in email you have to enable this 2 option.','track-geolocation-of-users-using-contact-form-7').'</h3>
						<p> <small>'. esc_html__('1) You have to enable "Use HTML content type" in email setting of Contact form 7.','track-geolocation-of-users-using-contact-form-7').'</small>	</p>
						<p> <small>'. esc_html__('2) You have to enable "Maps Static API" in Google Map API.','track-geolocation-of-users-using-contact-form-7').'</small>	</p>
					</td>
				</tr>
			</tfoot>
		</table>';
	}
	
	// Webhook API Test and Logs (outside form)
	if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-webhook-api"){
		echo '<div class="cfgeo-webhook-test">';
		echo '<h4>' . esc_html__( 'Test Webhook', 'track-geolocation-of-users-using-contact-form-7' ) . '</h4>';
		echo '<p>' . esc_html__( 'Click the button below to test your webhook configuration with sample data:', 'track-geolocation-of-users-using-contact-form-7' ) . '</p>';
		echo '<button type="button" id="test-webhook" class="cfgeo-submit-btn">' . esc_html__( 'Test Webhook', 'track-geolocation-of-users-using-contact-form-7' ) . '</button>';
		echo '<div id="webhook-test-result" style="margin-top: 10px; display: none;"></div>';
		echo '</div>';
		
		echo '<div class="cfgeo-webhook-logs">';
		echo '<h4>' . esc_html__( 'Webhook Logs', 'track-geolocation-of-users-using-contact-form-7' ) . '</h4>';
		echo '<p>' . esc_html__( 'Recent webhook delivery attempts:', 'track-geolocation-of-users-using-contact-form-7' ) . '</p>';
		echo '<div style="margin-bottom: 10px;">';
		echo '<button type="button" id="clear-webhook-logs" class="cfgeo-submit-btn">' . esc_html__( 'Clear Logs', 'track-geolocation-of-users-using-contact-form-7' ) . '</button>';
		echo '</div>';
		echo '<div id="webhook-logs" style="max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">';
		echo '<p class="description">' . esc_html__( 'No webhook logs available yet.', 'track-geolocation-of-users-using-contact-form-7' ) . '</p>';
		echo '</div>';
		echo '</div>';
	}
	?>
</div>
<?php

	// Localize the script with new data
	$cfgeo_translation_array = array(
		'ipstack'		=> esc_html( '<h3>ipstack API Access Key</h3>' .
								'<p>Enter Your ipstack API Access Key Which you can get it by signing up for Free Plan from <a href="https://ipstack.com/signup/free" target="_blank">here</a>.
								</p>','track-geolocation-of-users-using-contact-form-7' ),
		'google'		=> esc_html( '<h3>Google Map Geolocation API Key</h3>' .
								'<p>Get You Google Map API key from <a href="'.$cfgeo_google_api_link.'" target="_blank">here</a> and make sure "Maps Static API" is Enabled.
								</p>','track-geolocation-of-users-using-contact-form-7' ),
		'debug'			=> esc_html( '<h3>Debug Mode</h3>' .
								'<p>Enabling the debug mode will help us to track any issue with the API.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'graphcolor'	=> esc_html( '<h3>Color Picker</h3>' .
								'<p>Click on the textbox to Select the Color for Submission Graph.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'webhook_enabled'=> esc_html( '<h3>Enable Webhook API</h3>' .
								'<p>Enable this option to send geolocation data to external platforms via webhooks when form submissions occur.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'webhook_urls'	=> esc_html( '<h3>Webhook URLs</h3>' .
								'<p>Enter the webhook URLs where you want to send geolocation data. You can add multiple URLs, one per line.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'webhook_secret'	=> esc_html( '<h3>Webhook Secret Key</h3>' .
								'<p>Optional secret key for webhook authentication. This will be used to create a signature for webhook payloads.</p>','track-geolocation-of-users-using-contact-form-7' ),


		'form_graph_url'=> $cfgeo_form_graph_url,
		'google_api'	=> $cfgeo_google_api,
		'graph_color'	=> $cfgeo_graph_color,
		'cntry_data'	=> $cfgeo_data_arr_to_str
	);
	wp_enqueue_script( 'wp-pointer' );
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( CFGEO_PREFIX . '_spectrum_js' );
	wp_enqueue_style( CFGEO_PREFIX . '_spectrum_css' );
	wp_enqueue_style( CFGEO_PREFIX . '_admin_css' );
	wp_localize_script( CFGEO_PREFIX . '_admin_js', 'translate_string_geo', $cfgeo_translation_array );
	wp_enqueue_script( CFGEO_PREFIX . '_admin_js' );
