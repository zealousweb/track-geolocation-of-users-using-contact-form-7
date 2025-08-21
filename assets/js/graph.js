( function($) {
	"use strict";

	jQuery(document).ready( function($) {
		// console.log(translate_string_graph_geo.cntry_data);
		var cfgeo_cntry_data = JSON.parse(translate_string_graph_geo.cntry_data);

		google.charts.load('current', {
			'packages':['geochart'],
			// Note: you will need to get a mapsApiKey for your project.
			// See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
			'mapsApiKey': translate_string_graph_geo.google_api
		});

		google.charts.setOnLoadCallback(cfgeo_drawRegionsMap);
		var cfgeo_chartdata=[];
		var cfgeo_Header= ['Country', 'Entries'];
		cfgeo_chartdata.push(cfgeo_Header);
		for (var cfgeo_i = 0; cfgeo_i < cfgeo_cntry_data.length; cfgeo_i++) {
			var cfgeo_temp=[];
			 cfgeo_temp.push(cfgeo_cntry_data[cfgeo_i].ctrname);
			 cfgeo_temp.push(cfgeo_cntry_data[cfgeo_i].etr);
			 cfgeo_chartdata.push(cfgeo_temp);
		}
		 function cfgeo_drawRegionsMap() {
			 var cfgeo_data = new google.visualization.arrayToDataTable(cfgeo_chartdata);
			 var cfgeo_options = {keepAspectRatio:true,colorAxis: {colors: [translate_string_graph_geo.graph_color]}};
			 var cfgeo_chart = new google.visualization.GeoChart(document.getElementById('entry_submission_graph'));
			 cfgeo_chart.draw(cfgeo_data, cfgeo_options);
		}
	});

} )( jQuery );
