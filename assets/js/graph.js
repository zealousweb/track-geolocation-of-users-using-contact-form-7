( function($) {
	"use strict";

	jQuery(document).ready( function($) {
		// console.log(translate_string_graph_geo.cntry_data);
		var cntry_data = JSON.parse(translate_string_graph_geo.cntry_data);

		google.charts.load('current', {
			'packages':['geochart'],
			// Note: you will need to get a mapsApiKey for your project.
			// See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
			'mapsApiKey': translate_string_graph_geo.google_api
		});

		google.charts.setOnLoadCallback(drawRegionsMap);
		var chartdata=[];
		var Header= ['Country', 'Entries'];
		chartdata.push(Header);
		for (var i = 0; i < cntry_data.length; i++) {
			var temp=[];
			 temp.push(cntry_data[i].ctrname);
			 temp.push(cntry_data[i].etr);
			 chartdata.push(temp);
		}
		 function drawRegionsMap() {
			 var data = new google.visualization.arrayToDataTable(chartdata);
			 var options = {keepAspectRatio:true,colorAxis: {colors: [translate_string_graph_geo.graph_color]}};
			 var chart = new google.visualization.GeoChart(document.getElementById('entry_submission_graph'));
			 chart.draw(data, options);
		}
	});

} )( jQuery );
