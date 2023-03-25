/** Functions **/

function display_pie_chart() {

	$(".chart-wrapper").show();
    $("#chart_preview").css("width", 520);
    
    $("#chart_preview").kendoChart({
        theme: "blueopal",
        "title": {
        	visible: false,
            position: "top",
            align: "center",
            text: ""
        },
        chartArea: {"height": 300, background: ""},
        legend: {
            visible: true,
            position: "right" 
        },
        seriesDefaults: {
            labels: {
                visible: true,
                template: "#= kendo.format('{0:P}', percentage)#",
                align: "circle",
                position: "outsideEnd",
                background: ""
            }
        },
        series: [{
            type: "pie",
            startAngle: 90,
            data: [{"category":"Cat","value":40,"entry":"18 entries"},{"category":"Dog","value":11.11,"entry":"5 entries"},{"category":"Birds","value":8.89,"entry":"4 entries"},{"category":"Fish","value":17.78,"entry":"8 entries"},{"category":"Reptiles","value":11.11,"entry":"5 entries"},{"category":"Other","value":11.11,"entry":"5 entries"}]
        }],
        tooltip: {
            visible: true,
            template: "#= category # - #= dataItem.entry # - #= kendo.format('{0:P}', percentage)#"
        }
    });
}

function display_donut_chart() {

	$(".chart-wrapper").show();
    $("#chart_preview").css("width", 520);
    
    $("#chart_preview").kendoChart({
        theme: "blueopal",
        "title": {
        	visible: false,
            position: "top",
            align: "center",
            text: ""
        },
        chartArea: {"height": 300, background: ""},
        legend: {
            visible: true,
            position: "right" 
        },
        seriesDefaults: {
            labels: {
                visible: true,
                template: "#= kendo.format('{0:P}', percentage)#",
                align: "circle",
                position: "outsideEnd",
                background: ""
            }
        },
        series: [{
            type: "donut",
            startAngle: 90,
            data: [{"category":"Cat","value":40,"entry":"18 entries"},{"category":"Dog","value":11.11,"entry":"5 entries"},{"category":"Birds","value":8.89,"entry":"4 entries"},{"category":"Fish","value":17.78,"entry":"8 entries"},{"category":"Reptiles","value":11.11,"entry":"5 entries"},{"category":"Other","value":11.11,"entry":"5 entries"}]
        }],
        tooltip: {
            visible: true,
            template: "#= category # - #= dataItem.entry # - #= kendo.format('{0:P}', percentage)#"
        }
    });
}

function display_bar_chart() {

	$(".chart-wrapper").show();
	$("#chart_preview").css("width", 520);

	$("#chart_preview").kendoChart({
	    theme: "blueopal",
	    "title": {
	    	visible: false,
	        position: "top",
	        align: "center",
	        text: ""
	    },
	    chartArea: {"background":"","height":300},
	    legend: {
	        visible: true,
	        position: "right",
	        background: "" 
	    },
	    seriesDefaults: {
	        type: "column",
	        categoryField: "category",
	        stack: false,
	        labels: {
	            visible: true,
	            template: "#= dataItem.percentage # ",
	            align: "circle",
	            position: "outsideEnd",
	            background: ""
	        }
	    },
	    series: [{ color: "", data: [{"category":"Cat","value":10,"percentage":"29.41 %"},{"category":"Dog","value":5,"percentage":"14.70 %"},{"category":"Birds","value":4,"percentage":"11.76 %"},{"category":"Fish","value":8,"percentage":"23.52 %"},{"category":"Reptiles","value":5,"percentage":"14.70 %"},{"category":"Other","value":2,"percentage":"11.11 %"}] }],
	    tooltip: {
	        visible: true,
	        template: "#= category # - #= value # entries"
	    },
	    valueAxis: {
	        line: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        },
	        majorGridLines: {
	            visible: true
	        }
	    },
	    categoryAxis: {
	    	
	        line: {
	            visible: true
	        },
	        majorGridLines: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        }
	    }
	});
}

function display_line_chart() {

	$(".chart-wrapper").show();
	$("#chart_preview").css("width", 520);

	$("#chart_preview").kendoChart({
	    theme: "blueopal",
	    "title": {
	    	visible: false,
	        position: "top",
	        align: "center",
	        text: ""
	    },
	    chartArea: {"background":"","height":300},
	    legend: {
	        visible: true,
	        position: "top" 
	    },
	    seriesDefaults: {
	        type: "line",
	        categoryField: "category",
	        
	        style: "smooth",
	        
	        stack: false,
	        labels: {
	            visible: false,
	            template: "",
	            align: "circle",
	            position: "outsideEnd"
	        }
	    },
	    series: [{ color: "", data: [{"category":"January","value":18},{"category":"February","value":5},{"category":"March","value":3},{"category":"April","value":12},{"category":"May","value":5},{"category":"June","value":5}] }],
	    tooltip: {
	        visible: true,
	        template: "#= category # - #= value # entries"
	    },
	    valueAxis: {
	        line: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        },
	        majorGridLines: {
	            visible: true
	        }
	    },
	    categoryAxis: {
	    	
	        line: {
	            visible: true
	        },
	        majorGridLines: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        }
	    }
	});
}

function display_area_chart() {

	$(".chart-wrapper").show();
	$("#chart_preview").css("width", 520);

	$("#chart_preview").kendoChart({
	    theme: "blueopal",
	    "title": {
	    	visible: false,
	        position: "top",
	        align: "center",
	        text: ""
	    },
	    chartArea: {"background":"","height":300},
	    legend: {
	        visible: true,
	        position: "top" 
	    },
	    seriesDefaults: {
	        type: "area",
	        categoryField: "category",
	        
	        
	        area: { line: { style: "smooth" } },
	        stack: false,
	        labels: {
	            visible: false,
	            template: "#= dataItem.percentage # ",
	            align: "circle",
	            position: "outsideEnd"
	        }
	    },
	    series: [{ color: "", data: [{"category":"January","value":18},{"category":"February","value":5},{"category":"March","value":3},{"category":"April","value":12},{"category":"May","value":5},{"category":"June","value":5}] }],
	    tooltip: {
	        visible: true,
	        template: "#= category # - #= value # entries"
	    },
	    valueAxis: {
	        line: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        },
	        majorGridLines: {
	            visible: true
	        }
	    },
	    categoryAxis: {
	    	
	        line: {
	            visible: true
	        },
	        majorGridLines: {
	            visible: true
	        },
	        minorGridLines: {
	            visible: false
	        }
	    }
	});
}

function display_counter_chart(){
	$(".mf-chart-counter-wrapper").show();
}
function display_grid_chart(){
	$(".grid-wrapper").show();
}
function display_rating_chart(){
	$(".rating-wrapper").show();
}
/** end functions **/


$(function(){
	//load pie chart as the default
	display_pie_chart();

	//if the form doesn't have any datasource, make sure to disable the add widget button
	if($("#aw_select_datasource").length > 0){
		//show add widget button
		$(".add_widget_group").show();
	}else{
		//hide add widget button
		$(".add_widget_group").hide();
	}

	//remove 'allrows' from the datasource dropdown
	$("#aw_select_datasource option[value*='allrows']").remove();

	//attach event to "Select Widget Type" dropdown
	$('#aw_select_widget').bind('change', function() {
		var chart_type = $(this).val();

		$(".chart-wrapper,.grid-wrapper,.mf-chart-counter-wrapper,.rating-wrapper").hide();
		
		//display the selected chart preview
		if(chart_type == 'pie'){
			display_pie_chart();
		}else if(chart_type == 'donut'){
			display_donut_chart();
		}else if(chart_type == 'bar'){
			display_bar_chart();
		}else if(chart_type == 'line'){
			display_line_chart();
		}else if(chart_type == 'area'){
			display_area_chart();
		}else if(chart_type == 'counter'){
			display_counter_chart();
		}else if(chart_type == 'grid'){
			display_grid_chart();
		}else if(chart_type == 'rating'){
			display_rating_chart();
		}

		//display the correct field datasource dropdown, based on widget type
		$("#aw_select_field_info").show();
		
		$(".select_datasource_group").show();
		
		$("#select_datasource_span_simple,#select_datasource_span_expanded,#select_datasource_span_allfield,#aw_horizontal_axis_span,#select_datasource_span_rating_fields").hide();
		
		if(chart_type == 'line' || chart_type == 'area'){
			$("#select_datasource_span_expanded").show();

			$("#aw_horizontal_axis").val("date"); //reset to date horizontal axis

			$("#aw_horizontal_axis_span").show();
			$("#aw_horizontal_axis_span").css("display","block");
		}else if(chart_type == 'grid'){
			$("#select_datasource_span_allfield").show();
			
			//for grid, hide the datasource box completely
			$(".select_datasource_group").hide();
		}else if(chart_type == 'rating'){
			$("#select_datasource_span_rating_fields").show();
		}else{ //bar, donut, pie, counter
			
			//only area, bar and line support 'allrows'
			//so in this case, we need to remove 'allrows' selection if the chart type is not 'bar'
			var selected_datasource = $("#aw_select_datasource").val();
			
			$("#aw_select_datasource option").remove();
			$("#aw_select_datasource").append($("#aw_select_datasource_lookup option").clone());
			
			if(chart_type != 'bar'){
				$("#aw_select_datasource option[value*='allrows']").remove();
			}
			
			$("#aw_select_datasource").val(selected_datasource);
			$("#select_datasource_span_simple").show();
		}

		//if the form doesn't have any datasource, make sure to disable the add widget button
		if($("#aw_select_datasource").length > 0 ||  chart_type == 'grid'){
			//show add widget button
			$(".add_widget_group").slideDown();
		}else{
			//hide add widget button
			$(".add_widget_group").slideUp();
		}

	});

	//attach event to "Horizontal Axis" dropdown
	$('#aw_horizontal_axis').bind('change', function() {
		var axis_type = $(this).val();

		if(axis_type == 'date'){
			$("#select_datasource_span_simple").hide();
			$("#select_datasource_span_expanded").show();
		}else if(axis_type == 'category'){
			//add 'allrows' into the datasource dropdown if the axis is category
			var selected_datasource = $("#aw_select_datasource").val();
			
			$("#aw_select_datasource option").remove();
			$("#aw_select_datasource").append($("#aw_select_datasource_lookup option").clone());
			$("#aw_select_datasource").val(selected_datasource);
			
			$("#select_datasource_span_expanded").hide();
			$("#select_datasource_span_simple").show();
		}

	});

	//attach event to "Add Widget" button
	$("#button_add_widget").click(function(){
		
		if($("#button_add_widget").text() != 'Creating Widget...'){
				
				//display loader while saving
				$("#button_add_widget").prop("disabled",true);
				$("#button_add_widget").text('Creating Widget...');
				$("#add_widget_form").submit();
		}
		
		return false;
	});

});