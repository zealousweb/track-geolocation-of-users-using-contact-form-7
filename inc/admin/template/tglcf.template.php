<?php
/**
* Template Used for Settings Page
*
* @package WordPress
* @subpackage Track Geolocation Of Users Using Contact Form 7
* @since 2.0
**/

$form_graph_url    = admin_url("admin.php?page=geolocation-setting&tab=cfgeo-submission-graph&form-id=");
$google_api        = get_option('cfgeo_google_api_key');
$country_cnt       = $this->cfgeo_get_meta_values( 'cfgeo-country' );
$google_api_link   = 'https://developers.google.com/maps/documentation/geolocation/get-api-key';
$cfgeo_graph_color = get_option( 'cfgeo_color_picker' );

if($cfgeo_graph_color == ''){
	// if no color selected this color will be show by default
	$cfgeo_graph_color = '#0073aa';
}
if($country_cnt){
	$data_arr_to_str = $country_cnt;
}else{
	$data_arr_to_str = '';
}
$active_tab = "cfgeo-setting";
if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
	if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
		$active_tab = "cfgeo-setting";
	}elseif($_GET["tab"] == "cfgeo-submission-graph" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
		$active_tab = "cfgeo-submission-graph";
	}else{
		$active_tab = "cfgeo-shortcode-info";
	}
}
?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php echo esc_html( self::$activation_menuname ); ?></h2>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-setting' ); ?>" class="nav-tab <?php echo $active_tab == 'cfgeo-setting' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Geolocation Settings', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-submission-graph' ); ?>" class="nav-tab <?php echo esc_attr( $active_tab == 'cfgeo-submission-graph' ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Submission Graph', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
		<a href="<?php echo esc_url( '?page=geolocation-setting&tab=cfgeo-shortcode-info' ); ?>" class="nav-tab <?php echo esc_attr( $active_tab == 'cfgeo-shortcode-info' ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html__( 'Shortcode Info', 'track-geolocation-of-users-using-contact-form-7' ); ?></a>
	</h2>
	<?php settings_errors(); ?>
	<form method="post" action="options.php" class="setting-geolocation">
	<?php
		//add_settings_section callback is displayed here. For every new section we need to call settings_fields.
		settings_fields("cfgeo_googleapi");
		// all the add_settings_field callbacks is displayed here
		do_settings_sections(self::$setting_page);
		if(isset($_GET["tab"]) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
			if($_GET["tab"] == "cfgeo-setting" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){
				// Add the submit button to serialize the options
				submit_button();
			}
		}else{
			// Add the submit button to serialize the options
			submit_button();
		}
	?>
	</form>
	<?php
	if(isset($_GET["tab"]) && $_GET["tab"] == "cfgeo-submission-graph" || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )){ ?>
		<h3><?php esc_html__("A Detailed graph on the basis of submitted forms.",'track-geolocation-of-users-using-contact-form-7'); ?></h3>
		<?php
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
		$selected = ( isset( $_GET['form-id']) || isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ) , 'other_setting' )) ? sanitize_text_field($_GET['form-id']) : '' ;
		echo '<select name="form-id" id="form-id-graph">';
		echo '<option value="all">' . esc_html( 'All Forms', 'track-geolocation-of-users-using-contact-form-7' ) . '</option>';
		foreach ( $posts as $post ) {
			echo '<option value="' . esc_attr( $post->ID ) . '" ' . selected( $selected, $post->ID, false ) . '>' . esc_html( $post->post_title ) . '</option>';
		}
		echo '</select>';
		?>
		<div id="entry_submission_graph" style="margin-top:15px; width: 1080px; height: 500px;"></div>
		<?php
		$translation_graph_array = array(
			'google_api'	=> $google_api,
			'graph_color'	=> $cfgeo_graph_color,
			'cntry_data'	=> $data_arr_to_str
		);
		wp_localize_script( CFGEO_PREFIX . '_graph_js', 'translate_string_graph_geo', $translation_graph_array );
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
					<td> <strong>'. esc_html('Details You get in EMail.','track-geolocation-of-users-using-contact-form-7').'</strong> </td>
					<td> <strong>'. esc_html('Shortcode','track-geolocation-of-users-using-contact-form-7').'</strong> </td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>'. esc_html('To add latitude/longitude, country, state, city.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add latitude/longitude, country, state, city & Google map static image.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation lat-long country state city gmap]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add just latitude/longitude.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation lat-long]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add just country.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation country]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add just state.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation state]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add just city.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation city]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
				<tr>
					<td>'. esc_html('To add just Google map static image.','track-geolocation-of-users-using-contact-form-7').'</td>
					<td><input type="text" value="[geolocation gmap]" style="width: 100%;color: #000;" disabled=""></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">
						<h3>'. esc_html('Note: To add Google map static image in email you have to enable this 2 option.','track-geolocation-of-users-using-contact-form-7').'</h3>
						<p> <small>'. esc_html('1) You have to enable "Use HTML content type" in email setting of Contact form 7.','track-geolocation-of-users-using-contact-form-7').'</small>	</p>
						<p> <small>'. esc_html('2) You have to enable "Maps Static API" in Google Map API.','track-geolocation-of-users-using-contact-form-7').'</small>	</p>
					</td>
				</tr>
			</tfoot>
		</table>';
	}
	?>
</div>
<?php

	// Localize the script with new data
	$translation_array = array(
		'ipstack'		=> esc_html( '<h3>ipstack API Access Key</h3>' .
								'<p>Enter Your ipstack API Access Key Which you can get it by signing up for Free Plan from <a href="https://ipstack.com/signup/free" target="_blank">here</a>.
								</p>','track-geolocation-of-users-using-contact-form-7' ),
		'google'		=> esc_html( '<h3>Google Map Geolocation API Key</h3>' .
								'<p>Get You Google Map API key from <a href="'.$google_api_link.'" target="_blank">here</a> and make sure "Maps Static API" is Enabled.
								</p>','track-geolocation-of-users-using-contact-form-7' ),
		'debug'			=> esc_html( '<h3>Debug Mode</h3>' .
								'<p>Enabling the debug mode will help us to track any issue with the API.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'graphcolor'	=> esc_html( '<h3>Color Picker</h3>' .
								'<p>Click on the textbox to Select the Color for Submission Graph.</p>','track-geolocation-of-users-using-contact-form-7' ),
		'form_graph_url'=> $form_graph_url,
		'google_api'	=> $google_api,
		'graph_color'	=> $cfgeo_graph_color,
		'cntry_data'	=> $data_arr_to_str
	);
	wp_enqueue_script( 'wp-pointer' );
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( CFGEO_PREFIX . '_spectrum_js' );
	wp_enqueue_style( CFGEO_PREFIX . '_spectrum_css' );
	wp_enqueue_style( CFGEO_PREFIX . '_admin_css' );
	wp_localize_script( CFGEO_PREFIX . '_admin_js', 'translate_string_geo', $translation_array );
	wp_enqueue_script( CFGEO_PREFIX . '_admin_js' );
?>