( function($) {
	"use strict";

	jQuery(document).ready( function($) {
		jQuery('#cfgeo_color_picker').spectrum({
			type: "component",
			showPalette: false,
			showSelectionPalette: true,
			togglePaletteOnly: true,
			hideAfterPaletteSelect: true,
			showAlpha: false,
			change: function(color) {
				if(color != null){
					var col = color.toHexString();
					if(col.length == 7){
						jQuery(".cfgeo_color_picker").removeClass('validation-error-geo');
					}
				}else{
					jQuery(".cfgeo_color_picker").addClass('validation-error-geo');
					return false;
				}
			}
		});

		jQuery('.setting-geolocation input#submit').click( function() {
			var color_val = jQuery(".cfgeo_color_picker").val();
			if(jQuery( ".cfgeo_color_picker" ).hasClass( "validation-error-geo" ) || color_val == null || color_val == ''){
				jQuery(".cfgeo_color_picker").addClass('validation-error-geo');
				return false;
			}else if(color_val.length != 7){
				jQuery(".cfgeo_color_picker").addClass('validation-error-geo');
				return false;
			}
		});

		jQuery("#form-id-graph").change(function(){
			window.location= translate_string_geo.form_graph_url + this.value;
		});

		jQuery( '#cfgeo-ipstack' ).on( 'mouseenter click', function() {

			jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
			jQuery( '#cfgeo-ipstack' ).pointer({
				pointerClass: 'wp-pointer cfgeo-pointer',
				content: translate_string_geo.ipstack,
				position: 'left center',
			} ).pointer('open');
		});

		jQuery( '#cfgeo-google' ).on( 'mouseenter click', function() {

			jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
			jQuery( '#cfgeo-google' ).pointer({
				pointerClass: 'wp-pointer cfgeo-pointer',
				content: translate_string_geo.google,
				position: 'left center',
			} ).pointer('open');
		});

		jQuery( '#cfgeo-debug' ).on( 'mouseenter click', function() {

			jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
			jQuery( '#cfgeo-debug' ).pointer({
				pointerClass: 'wp-pointer cfgeo-pointer',
				content: translate_string_geo.debug,
				position: 'left center',
			} ).pointer('open');
		});

		jQuery( '#cfgeo-color-graph' ).on( 'mouseenter click', function() {

			jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
			jQuery( '#cfgeo-color-graph' ).pointer({
				pointerClass: 'wp-pointer cfgeo-pointer',
				content: translate_string_geo.graphcolor,
				position: 'left center',
			} ).pointer('open');
		});
	});

} )( jQuery );
