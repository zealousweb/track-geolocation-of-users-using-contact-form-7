( function($) {
	"use strict";

	jQuery(document).ready( function($) {
		// Debug: Check if cfgeo_ajax is available
		console.log('CFGEO Debug: cfgeo_ajax available:', typeof cfgeo_ajax !== 'undefined');
		if (typeof cfgeo_ajax !== 'undefined') {
			console.log('CFGEO Debug: cfgeo_ajax data:', cfgeo_ajax);
		}
		
		// Debug: Check if we're on the CFGEO page
		console.log('CFGEO Debug: Advanced filters container found:', $('.cfgeo-advanced-filters').length);
			// Initialize spectrum color picker if available
	if (jQuery.fn.spectrum) {
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
	} else {
		console.log('CFGEO Debug: Spectrum plugin not available, skipping color picker initialization');
	}

		jQuery('.setting-geolocation input#submit').click( function() {
			// Only validate color picker if spectrum is available
			if (jQuery.fn.spectrum) {
				var color_val = jQuery(".cfgeo_color_picker").val();
				if(jQuery( ".cfgeo_color_picker" ).hasClass( "validation-error-geo" ) || color_val == null || color_val == ''){
					jQuery(".cfgeo_color_picker").addClass('validation-error-geo');
					return false;
				}else if(color_val.length != 7){
					jQuery(".cfgeo_color_picker").addClass('validation-error-geo');
					return false;
				}
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

		// Advanced Filter JavaScript for CFGEO Admin Panel
		// Handles AJAX filtering, debouncing, pagination, and sorting
		
		var filterTimeout;
		var isLoading = false;

		// Function to perform AJAX filtering
		function performAjaxFilter() {
			console.log('performAjaxFilter called');
			if (isLoading) {
				console.log('Already loading, skipping');
				return;
			}
			
			// Check if we're on the correct page
			if (!$('.cfgeo-advanced-filters').length) {
				console.log('Not on CFGEO page, skipping');
				return;
			}
			
			isLoading = true;
			
			// Show loading states
			$('.cfgeo-advanced-filters').addClass('loading');
			$('.cfgeo-loading').show();
			$('.cfgeo-filter-row input, .cfgeo-filter-row select').prop('disabled', true);
			$('.wp-list-table tbody').html('<tr><td colspan="8" style="text-align: center; padding: 20px;"><div class="spinner is-active"></div> Loading...</td></tr>');
			
			var filterData = {
				action: 'cfgeo_filter_submissions',
				nonce: cfgeo_ajax.nonce,
				form_id: $('#form-id').val() || 'all',
				country_filter: $('#country-filter').val() || '',
				city_filter: $('#city-filter').val() || '',
				date_from: $('#date-from').val() || '',
				date_to: $('#date-to').val() || '',
				search_term: $('#search-term').val() || '',
				orderby: $('input[name="orderby"]').val() || 'date',
				order: $('input[name="order"]').val() || 'DESC',
				paged: $('input[name="paged"]').val() || '1'
			};

			console.log('Sending AJAX request with data:', filterData);
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: filterData,
				success: function(response) {
					if (response.success) {
						// Update the table body
						$('.wp-list-table tbody').html(response.data.html);
						
						// Update pagination
						if (response.data.pagination) {
							$('.tablenav-pages').html(response.data.pagination);
						} else {
							$('.tablenav-pages').html('');
						}
						
						// Update filter count
						$('.cfgeo-filter-count').remove();
						if (response.data.total > 0) {
							var filterInfo = $('<div class="cfgeo-filter-count">Showing ' + response.data.total + ' submission(s)</div>');
							$('.cfgeo-advanced-filters').append(filterInfo);
						} else {
							var filterInfo = $('<div class="cfgeo-filter-count">No submissions found matching your criteria</div>');
							$('.cfgeo-advanced-filters').append(filterInfo);
						}
						
						// Update URL without page reload
						var newUrl = new URL(window.location);
						newUrl.searchParams.set('form-id', filterData.form_id);
						newUrl.searchParams.set('country-filter', filterData.country_filter);
						newUrl.searchParams.set('city-filter', filterData.city_filter);
						newUrl.searchParams.set('date-from', filterData.date_from);
						newUrl.searchParams.set('date-to', filterData.date_to);
						newUrl.searchParams.set('search-term', filterData.search_term);
						newUrl.searchParams.set('orderby', filterData.orderby);
						newUrl.searchParams.set('order', filterData.order);
						newUrl.searchParams.set('paged', filterData.paged);
						
						window.history.pushState({}, '', newUrl);
					} else {
						alert('Error: ' + response.data);
					}
				},
				error: function(xhr, status, error) {
					console.log('AJAX Error:', xhr.responseText);
					alert(cfgeo_ajax.error_message);
				},
				complete: function() {
					isLoading = false;
					$('.cfgeo-advanced-filters').removeClass('loading');
					$('.cfgeo-loading').hide();
					$('.cfgeo-filter-row input, .cfgeo-filter-row select').prop('disabled', false);
				}
			});
		}

		// Only initialize AJAX functionality if we're on the CFGEO page
		if ($('.cfgeo-advanced-filters').length && typeof cfgeo_ajax !== 'undefined') {
			console.log('CFGEO Debug: Initializing AJAX functionality');
			
			// Real-time filtering with debounce
			$('#form-id, #country-filter, #city-filter, #date-from, #date-to').on('change', function() {
				console.log('Filter changed:', $(this).attr('id'), $(this).val());
				clearTimeout(filterTimeout);
				filterTimeout = setTimeout(performAjaxFilter, 300);
			});

			// Search with debounce
			$('#search-term').on('input', function() {
				console.log('Search input:', $(this).val());
				clearTimeout(filterTimeout);
				filterTimeout = setTimeout(performAjaxFilter, 500);
			});

			// Search with Enter key
			$('#search-term').on('keypress', function(e) {
				if (e.which === 13) {
					clearTimeout(filterTimeout);
					performAjaxFilter();
				}
			});

			// Clear filters functionality
			$('.cfgeo-clear-filters').on('click', function(e) {
				e.preventDefault();
				
				// Clear all filter values
				$('#form-id').val('all');
				$('#country-filter').val('');
				$('#city-filter').val('');
				$('#date-from').val('');
				$('#date-to').val('');
				$('#search-term').val('');
				$('input[name="orderby"]').val('date');
				$('input[name="order"]').val('DESC');
				$('input[name="paged"]').val('1');
				
				// Perform filter
				performAjaxFilter();
			});

			// Date validation
			$('#date-from, #date-to').on('change', function() {
				var fromDate = $('#date-from').val();
				var toDate = $('#date-to').val();
				
				if (fromDate && toDate && fromDate > toDate) {
					alert(cfgeo_ajax.date_error_message);
					$(this).val('');
				}
			});

			// Handle pagination clicks
			$(document).on('click', '.tablenav-pages a', function(e) {
				e.preventDefault();
				var paged = $(this).data('page') || '1';
				
				$('input[name="paged"]').val(paged);
				performAjaxFilter();
			});

			// Handle sorting clicks
			$(document).on('click', '.wp-list-table th.sortable a, .wp-list-table th.sorted a', function(e) {
				e.preventDefault();
				var href = $(this).attr('href');
				var url = new URL(href);
				var orderby = url.searchParams.get('orderby') || 'date';
				var order = url.searchParams.get('order') || 'DESC';
				
				$('input[name="orderby"]').val(orderby);
				$('input[name="order"]').val(order);
				$('input[name="paged"]').val('1'); // Reset to first page when sorting
				performAjaxFilter();
			});

			// Initial filter count display
			var totalPosts = $('.wp-list-table tbody tr').length;
			if (totalPosts > 0) {
				var filterInfo = $('<div class="cfgeo-filter-count">Showing ' + totalPosts + ' submission(s)</div>');
				$('.cfgeo-advanced-filters').append(filterInfo);
			}
		} else {
			console.log('CFGEO Debug: Not on CFGEO page or cfgeo_ajax not available, skipping AJAX initialization');
		}
	});

} )( jQuery );
