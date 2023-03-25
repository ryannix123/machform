<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/

 	//Generate markup for Graphic Charts (Pie, Donut)
 	function mf_display_chart($dbh,$chart_access_key){
 		
 		//change to 'canvas' if you want faster performance (but less interactivity)
 		$chart_rendering_engine = 'svg'; 

		$query = "SELECT 
						form_id,
						chart_id,
						chart_datasource,
						chart_type,
						chart_enable_filter,
						chart_filter_type,
						chart_title,
						chart_title_position,
						chart_title_align,
						chart_width,
						chart_height,
						chart_background,
						chart_theme,
						chart_legend_visible,
						chart_legend_position,
						chart_labels_visible,
						chart_labels_position,
						chart_labels_template,
						chart_labels_align,
						chart_tooltip_visible,
						chart_tooltip_template,
						chart_gridlines_visible,
						chart_bar_color,
						chart_is_stacked,
						chart_is_vertical,
						chart_line_style,
						chart_axis_is_date,
						chart_date_range,
						chart_date_period_value,
						chart_date_period_unit,
						chart_date_axis_baseunit,
						chart_date_range_start,
						chart_date_range_end   
				    FROM
				    	".MF_TABLE_PREFIX."report_elements
				   WHERE
				   		access_key = ?";
		$params = array($chart_access_key);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$form_id  				= (int) $row['form_id'];
		$chart_id 				= (int) $row['chart_id'];

		//format: element_x or element_x_allrows to display complete matrix field
		//the 'allrows' is only applicable for matrix field for area, bar and line chart
		//the 'allrows' is not available when the chart is using date axis
		$chart_datasource 		= $row['chart_datasource']; 
		
		$chart_type 			= $row['chart_type'];
		$chart_enable_filter 	= (int) $row['chart_enable_filter'];
		$chart_filter_type		= $row['chart_filter_type'];
		$chart_title 			= trim($row['chart_title']);
		$chart_title_position 	= $row['chart_title_position'];
		$chart_title_align 		= $row['chart_title_align'];
		$chart_width 			= (int) $row['chart_width'];
		$chart_height 			= (int) $row['chart_height'];
		$chart_theme 			= $row['chart_theme'];
		$chart_background 		= $row['chart_background'];
		$chart_legend_visible 	= (int) $row['chart_legend_visible'];
		$chart_legend_position 	= $row['chart_legend_position'];
		$chart_labels_visible 	= (int) $row['chart_labels_visible'];
		$chart_labels_template 	= $row['chart_labels_template'];
		$chart_labels_align 	= $row['chart_labels_align'];
		$chart_labels_position 	= $row['chart_labels_position'];
		$chart_tooltip_visible 	= (int) $row['chart_tooltip_visible'];
		$chart_tooltip_template = $row['chart_tooltip_template'];
		$chart_gridlines_visible = (int) $row['chart_gridlines_visible'];
		$chart_bar_color 		= $row['chart_bar_color'];
		$chart_is_stacked		= (int) $row['chart_is_stacked'];
		$chart_is_vertical		= (int) $row['chart_is_vertical'];
		$chart_line_style 		= $row['chart_line_style'];
		
		$chart_axis_is_date			= (int) $row['chart_axis_is_date'];
		$chart_date_range 			= $row['chart_date_range']; //possible values: all - period - custom
		$chart_date_period_value 	= (int) $row['chart_date_period_value'];
		$chart_date_period_unit 	= $row['chart_date_period_unit']; //possible values: day - week - month - year
		$chart_date_axis_baseunit 	= $row['chart_date_axis_baseunit']; //possible values: day - week - month - year, or leave it empty
		$chart_date_range_start 	= $row['chart_date_range_start'];
		$chart_date_range_end 		= $row['chart_date_range_end'];
						
		//get the datasource field type and property
		$exploded = explode('_',$chart_datasource);
		$datasource_element_id = (int) $exploded[1];

		$chart_using_date_axis = false;
		if(($chart_type == 'line' || $chart_type == 'area') && !empty($chart_axis_is_date)){
			//if the chart type is line or area and the axis is date, then the $chart_datasource will contain something like 'element_x_y'
			//where 'y' is the option id of the checkbox/radio/select field
			//'y' could also contain 'other'
			$datasource_option_id = $exploded[2];
			$chart_using_date_axis = true;
		}

		if(!empty($exploded[2]) && $exploded[2] == 'allrows'){
			//if the datasource is something like 'element_3_allrows', this means we're displaying a complete matrix field (all rows)
			$element_matrix_display_allrows = true;
		}
		
		$query = "select 
						element_type,
						element_choice_has_other,
						element_choice_other_label,
						element_matrix_parent_id,
						element_matrix_allow_multiselect,
						element_constraint 
					from 
						".MF_TABLE_PREFIX."form_elements 
				   where 
				   		form_id = ? and element_id = ?";
		$params = array($form_id,$datasource_element_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		
		$datasource_element_type 	= $row['element_type'];
		$element_choice_has_other   = (int) $row['element_choice_has_other'];
		$element_choice_other_label = $row['element_choice_other_label'];
		$element_matrix_parent_id 	= $row['element_matrix_parent_id'];
		$element_matrix_allow_multiselect = $row['element_matrix_allow_multiselect'];
		$element_constraint 		= $row['element_constraint'];

		//get filter/where clause if enabled for this chart
		if(!empty($chart_enable_filter)){
			$chart_where_clause = mf_get_chart_filter($dbh,$form_id,$chart_id,$chart_filter_type);
		}else{
			$chart_where_clause = "WHERE `status`=1";
		}

		$chart_area_obj = new stdClass();
		$chart_area_obj->background = $chart_background;

		//set chart area dimension
		if(!empty($chart_height) || !empty($chart_width)){
	
			$chart_area_obj->height = $chart_height;
			if(!empty($chart_width)){
				$chart_area_obj->width = $chart_width;
			}
		}
		
		$chart_area_json = json_encode($chart_area_obj);
		$chart_area_json = "chartArea: {$chart_area_json},";

		//set visibility of chart elements
		$chart_title_visible 	= !empty($chart_title) ? "true" : "false";
		$chart_legend_visible 	= !empty($chart_legend_visible) ? "true" : "false";
		$chart_labels_visible 	= !empty($chart_labels_visible) ? "true" : "false";
		$chart_tooltip_visible 	= !empty($chart_tooltip_visible) ? "true" : "false";
		$chart_gridlines_visible = !empty($chart_gridlines_visible) ? "true" : "false";
		$chart_is_stacked 		= !empty($chart_is_stacked) ? "true" : "false";

		//set chart series type
		if($chart_type == 'pie'){
			$chart_series_type = 'pie';
		}else if($chart_type == 'donut'){
			$chart_series_type = 'donut';
		}else if($chart_type == 'bar'){
			if(!empty($chart_is_vertical)){
				$chart_series_type = 'bar';
			}else{
				$chart_series_type = 'column';
			}
		}else if($chart_type == 'line'){
			$chart_series_type = 'line';
		}else if($chart_type == 'area'){
			$chart_series_type = 'area';
		}

		//if the element type is matrix, we need to convert it to either radio or checkbox
		//since matrix field is basically multiple radio/checkbox fields
		if($datasource_element_type == 'matrix'){
			if(!empty($element_matrix_parent_id)){
				//this is the child matrix field (any other row than the first one of the matrix)
				$query = "select 
								element_matrix_allow_multiselect 
							from 
								".MF_TABLE_PREFIX."form_elements 
						   where 
						   		form_id = ? and element_id = ?";
				$params = array($form_id,$element_matrix_parent_id);
				
				$sth = mf_do_query($query,$params,$dbh);
				$row = mf_do_fetch_result($sth);

				$element_matrix_allow_multiselect = (int) $row['element_matrix_allow_multiselect'];
			}else{
				//this is the first row of the matrix
				//check for 'allrows' property
				if($element_matrix_display_allrows === true){
					//get all child rows element id
					$exploded = array();
					$exploded = explode(',', $element_constraint);
					$datasource_element_id_array = array_merge((array) $datasource_element_id,$exploded);
				}
			}

			if(!empty($element_matrix_allow_multiselect)){
				$datasource_element_type = 'checkbox';
			}else{
				$datasource_element_type = 'radio';
			}

		}

		//start building the data series -------------------------
		if(empty($datasource_element_id_array)){
			$datasource_element_id_array = array($datasource_element_id);
		}

		$total_datasource 		   = count($datasource_element_id_array);
		$data_series_array_allrows = array();
		$whole_chart_total_percentage = 0;

		foreach ($datasource_element_id_array as $datasource_element_id) {
			
			$chart_datasource = 'element_'.$datasource_element_id;

			if($chart_using_date_axis){ //only line and area chart able to use date axis
				
				//get the date range and build the GROUP clause
				switch ($chart_date_axis_baseunit) {
					case 'day':
						$date_range_group_clause = "date_format(A.date_created,'%Y/%m/%d')";
						break;
					case 'week':
						$date_range_group_clause = "date_format(A.date_created,'%Y/%m/%d')"; //week is the same as day
						break;
					case 'month':
						$date_range_group_clause = "date_format(A.date_created,'%Y/%m')";
						break;
					case 'year':
						$date_range_group_clause = "date_format(A.date_created,'%Y')";
						break;
					default:
						$date_range_group_clause = "date_format(A.date_created,'%Y/%m/%d')";
						break;
				}

				//get the date range and build the WHERE clause
				if($chart_date_range == 'all'){
					$date_range_where_clause = '';
				}else if($chart_date_range == 'period'){
					$date_range_where_clause = "AND (A.date_created between (now() - interval {$chart_date_period_value} {$chart_date_period_unit}) and now())";			
				}else if($chart_date_range == 'custom'){
					$date_range_where_clause = "AND (A.date_created between '{$chart_date_range_start}' and '{$chart_date_range_end}')";	
				}

				if($datasource_element_type == 'radio' || $datasource_element_type == 'select' || $datasource_element_type == 'rating'){
					//get the total count for each date or month or year
					if($datasource_option_id == 'other'){
						$field_where_clause = "(A.{$chart_datasource}_other IS NOT NULL AND A.{$chart_datasource}_other <> '')  {$date_range_where_clause}";
					}else{
						$field_where_clause = "A.{$chart_datasource} = ? {$date_range_where_clause}";
					}
					
					$query = "SELECT 
									date_format(A.date_created,'%Y/%m/%d') entry_date,
									count(A.{$chart_datasource}) total_entry 
								FROM 
									(
										SELECT * FROM ".MF_TABLE_PREFIX."form_{$form_id} {$chart_where_clause}
									) A
							   WHERE 
							   	    {$field_where_clause} 	
							GROUP BY 
									{$date_range_group_clause}";
					$params = array($datasource_option_id);
					
				}else if($datasource_element_type == 'checkbox'){
					//get the total count for each date or month or year
					if($datasource_option_id == 'other'){
						$field_where_clause = "(A.{$chart_datasource}_other IS NOT NULL AND A.{$chart_datasource}_other <> '') {$date_range_where_clause}";
					}else{
						$field_where_clause = "A.{$chart_datasource}_{$datasource_option_id} = 1 {$date_range_where_clause}";
					}
					
					$query = "SELECT 
									date_format(A.date_created,'%Y/%m/%d') entry_date,
									count(A.{$chart_datasource}_{$datasource_option_id}) total_entry 
								FROM 
									(
										SELECT * FROM ".MF_TABLE_PREFIX."form_{$form_id} {$chart_where_clause}
									) A
							   WHERE 
							   	    {$field_where_clause} 
							GROUP BY 
									{$date_range_group_clause}";
					$params = array();
				}

				$data_series_array = array();					
				$sth = mf_do_query($query,$params,$dbh);

				while($row = mf_do_fetch_result($sth)){
					$data_object = new stdClass();
					$data_object->date 	= 'new Date('.$row['entry_date'].')';
					$data_object->value = $row['total_entry'];

					$data_series_array[] = '{date: new Date("'.$row['entry_date'].'"), value: '.$row['total_entry'].'}';
				}

				$data_series_joined = implode(',', $data_series_array);

				//chart using date axis only support 1 field at a time
				$data_series_json = '['.$data_series_joined.']';

			}else{
				if($datasource_element_type == 'radio' || $datasource_element_type == 'select'){
						
					//get all options of the radio
					$query = "SELECT 
									option_id,
									`option`
							    FROM 
							    	".MF_TABLE_PREFIX."element_options 
							   WHERE 
							   		form_id = ? and element_id = ? and live = 1 
							ORDER BY 
									`position` asc";
					$params = array($form_id,$datasource_element_id);
					$sth = mf_do_query($query,$params,$dbh);
					
					$option_label_array = array();
					while($row = mf_do_fetch_result($sth)){
						$option_label = $row['option'];
						$option_id    = $row['option_id'];
						
						$option_label_array[$option_id] = $option_label;
					}

					//if the field have 'other', we need to get the 'other' label
					if(!empty($element_choice_has_other)){
						$option_label_array[0] = $element_choice_other_label;
					}

					//get the data for each option 
					$query = "SELECT 
									{$chart_datasource} `option_id`,
									count({$chart_datasource}) total_entry 
								FROM 
									".MF_TABLE_PREFIX."form_{$form_id} A 
							   		{$chart_where_clause} 
							GROUP BY 
									{$chart_datasource}";
					
					$params = array();
					$sth = mf_do_query($query,$params,$dbh);

					$option_total_array = array();
					$all_entry_total = 0;
					while($row = mf_do_fetch_result($sth)){
						$option_total = $row['total_entry'];
						$option_id    = $row['option_id'];
						
						//if option_id is empty or zero, don't count it
						if(empty($option_id)){
							continue;
						}

						//if the label for the option is empty, don't count it
						//most likely the option has been deleted
						if(empty($option_label_array[$option_id])){
							continue;
						}

						$option_total_array[$option_id] = $option_total;
						$all_entry_total += $option_total;
					}

					//if the field have 'other', we need to calculate the total
					if(!empty($element_choice_has_other)){
						$query = "SELECT
										{$chart_datasource} `option_id`, 
										count({$chart_datasource}) total_entry 
									FROM 
										".MF_TABLE_PREFIX."form_{$form_id} A
								   		{$chart_where_clause} AND ({$chart_datasource}_other IS NOT NULL and {$chart_datasource} = 0)
								GROUP BY 
										{$chart_datasource}";
						
						$params = array();
						$sth = mf_do_query($query,$params,$dbh);

						while($row = mf_do_fetch_result($sth)){
							$option_total = $row['total_entry'];
							$option_id    = $row['option_id'];

							$option_total_array[$option_id] = $option_total;
							$all_entry_total += $option_total;
						}
					}

					//prevent division by zero when the field has no entry
					if(empty($all_entry_total)){
						$all_entry_total = 0.00001;
					}

					//construct the data series
					$data_series_array = array();
					
					foreach($option_label_array as $option_id=>$option_label){
						$option_total_array[$option_id] = $option_total_array[$option_id] ?? 0;

						$total_percentage = ($option_total_array[$option_id] / $all_entry_total) * 100;
						$total_percentage = round($total_percentage,2);

						$data_object = new stdClass();
						$data_object->category 	= $option_label;
						
						if(in_array($chart_type, array('pie','donut'))){
							$data_object->value 	= $total_percentage;
							$data_object->entry     = (int) $option_total_array[$option_id];
						}else if(in_array($chart_type, array('bar','line','area'))){
							$data_object->value 	 = (int) $option_total_array[$option_id];
							$data_object->percentage = $total_percentage.' %';
						}
						$whole_chart_total_percentage += $total_percentage;
						$data_series_array[] = $data_object; 
					}

					if($total_datasource == 1){
						$data_series_json = json_encode($data_series_array);
					}else{
						$data_series_array_allrows[] = $data_series_array;
					}
				}else if($datasource_element_type == 'rating'){
					
					$query = "SELECT 
									element_rating_max
							    FROM 
							    	".MF_TABLE_PREFIX."form_elements 
							   WHERE 
							   		form_id = ? and element_id = ?";
					$params = array($form_id,$datasource_element_id);
					$sth = mf_do_query($query,$params,$dbh);
					$row = mf_do_fetch_result($sth);

					$element_rating_max = (int) $row['element_rating_max'];

					$option_label_array = array();
					for($i=1;$i<=$element_rating_max;$i++){
						$option_label = $i;
						$option_id    = $i;
						
						$option_label_array[$option_id] = $option_label;
					}

					
					//get the data for each option 
					$query = "SELECT 
									{$chart_datasource} `option_id`,
									count({$chart_datasource}) total_entry 
								FROM 
									".MF_TABLE_PREFIX."form_{$form_id} A 
							   		{$chart_where_clause} 
							GROUP BY 
									{$chart_datasource}";
				
					$params = array();
					$sth = mf_do_query($query,$params,$dbh);

					$option_total_array = array();
					$all_entry_total = 0;
					while($row = mf_do_fetch_result($sth)){
						$option_total = $row['total_entry'];
						$option_id    = $row['option_id'];
						
						//if option_id is empty or zero, don't count it
						if(empty($option_id)){
							continue;
						}

						$option_total_array[$option_id] = $option_total;
						$all_entry_total += $option_total;
					}
					
					//prevent division by zero when the field has no entry
					if(empty($all_entry_total)){
						$all_entry_total = 0.00001;
					}

					//construct the data series
					$data_series_array = array();
					
					foreach($option_label_array as $option_id=>$option_label){
						$option_total_array[$option_id] = $option_total_array[$option_id] ?? 0;

						$total_percentage = ($option_total_array[$option_id] / $all_entry_total) * 100;
						$total_percentage = round($total_percentage,2);

						$data_object = new stdClass();
						$data_object->category 	= $option_label;
						
						if(in_array($chart_type, array('pie','donut'))){
							$data_object->value 	= $total_percentage;
							$data_object->entry     = (int) $option_total_array[$option_id];
						}else if(in_array($chart_type, array('bar','line','area'))){
							$data_object->value 	 = (int) $option_total_array[$option_id];
							$data_object->percentage = $total_percentage.' %';
						}
						$whole_chart_total_percentage += $total_percentage;
						$data_series_array[] = $data_object; 
					}

					if($total_datasource == 1){
						$data_series_json = json_encode($data_series_array);
					}else{
						$data_series_array_allrows[] = $data_series_array;
					}
				}else if($datasource_element_type == 'checkbox'){
					//get all options of the checkbox
					$query = "SELECT 
									option_id,
									`option`
							    FROM 
							    	".MF_TABLE_PREFIX."element_options 
							   WHERE 
							   		form_id = ? and element_id = ? and live = 1
							ORDER BY 
									`position` asc";
					$params = array($form_id,$datasource_element_id);
					$sth = mf_do_query($query,$params,$dbh);
					
					$option_label_array = array();
					while($row = mf_do_fetch_result($sth)){
						$option_label = $row['option'];
						$option_id    = $row['option_id'];
						
						$option_label_array[$option_id] = strip_tags($option_label);
					}

					//if the field have 'other', we need to get the 'other' label
					if(!empty($element_choice_has_other)){
						$option_label_array[0] = $element_choice_other_label;
					}

					//get the data for each option
					$option_total_array = array();
					$all_entry_total = 0;

					foreach ($option_label_array as $option_id => $option_label) {
						if(!empty($option_id)){
							$query = "SELECT 
											count({$chart_datasource}_{$option_id}) total_entry 
										FROM 
											".MF_TABLE_PREFIX."form_{$form_id} A
									   		{$chart_where_clause} AND ({$chart_datasource}_{$option_id} <> 0)";
						}else{
							//if option_id = 0, this is 'other' field
							//we need to have different query condition
							$query = "SELECT 
											count({$chart_datasource}_other) total_entry 
										FROM 
											".MF_TABLE_PREFIX."form_{$form_id} A 
									   		{$chart_where_clause} AND ({$chart_datasource}_other IS NOT NULL and {$chart_datasource}_other <> '')";
						}

						$params = array();
						$sth = mf_do_query($query,$params,$dbh);
						$row = mf_do_fetch_result($sth);
						
						$option_total = $row['total_entry'];
							
						$option_total_array[$option_id] = $option_total;
						$all_entry_total += $option_total;
						
					}

					//prevent division by zero when the field has no entry
					if(empty($all_entry_total)){
						$all_entry_total = 0.00001;
					}

					//construct the data series
					$data_series_array = array();
					
					foreach($option_label_array as $option_id=>$option_label){
						$total_percentage = ($option_total_array[$option_id] / $all_entry_total) * 100;
						$total_percentage = round($total_percentage,2);

						$data_object = new stdClass();
						$data_object->category 	= $option_label;
						
						if(in_array($chart_type, array('pie','donut'))){
							$data_object->value 	= $total_percentage;
							$data_object->entry     = (int) $option_total_array[$option_id];
						}else if(in_array($chart_type, array('bar','line','area'))){
							$data_object->value 	 = (int) $option_total_array[$option_id];
							$data_object->percentage = $total_percentage.' %';
						}
						$whole_chart_total_percentage += $total_percentage;
						$data_series_array[] = $data_object; 
					}

					if($total_datasource == 1){
						$data_series_json = json_encode($data_series_array);
					}else{
						$data_series_array_allrows[] = $data_series_array;
					}
				}
			}
		}
		//end building the data series ---------------------------

		//build the init code for each chart type
		//if the data is still empty, hide the labels to prevent NaN being displayed
		if(empty($whole_chart_total_percentage)){
			$chart_labels_template = '';
		}

		if(in_array($chart_type, array('pie','donut'))){
			$chart_init_code =<<<EOT
						theme: "{$chart_theme}",
						renderAs: "{$chart_rendering_engine}",
		                "title": {
		                	visible: {$chart_title_visible},
		                    position: "{$chart_title_position}",
		                    align: "{$chart_title_align}",
		                    text: "{$chart_title}"
		                },
		                {$chart_area_json}
		                legend: {
		                    visible: {$chart_legend_visible},
		                    position: "{$chart_legend_position}" 
		                },
		                seriesDefaults: {
		                    labels: {
		                        visible: {$chart_labels_visible},
		                        template: "{$chart_labels_template}",
		                        align: "{$chart_labels_align}",
		                        position: "{$chart_labels_position}"
		                    }
		                },
		                series: [{
		                    type: "{$chart_series_type}",
		                    startAngle: 90,
		                    data: {$data_series_json}
		                }],
		                tooltip: {
		                    visible: {$chart_tooltip_visible},
		                    template: "{$chart_tooltip_template}"
		                }
EOT;

		}else if(in_array($chart_type, array('bar','line','area'))){
			
			if(!empty($data_series_array_allrows)){
				//if the datasource is matrix field and we need to display all rows

				//get matrix rows title
				$query = "select element_title from (
													 (select 
															 element_title,element_position 
														from 
														 	 ".MF_TABLE_PREFIX."form_elements 
													   where 
															 form_id = ? and 
															 element_id = ?)
													  UNION
													 (select 
													 		 element_title,element_position 
													    from 
													    	 ".MF_TABLE_PREFIX."form_elements 
													   where 
													   		 form_id = ? and 
													   		 element_matrix_parent_id = ? and 
													   		 element_status = 1 
													order by 
															 element_position asc)) A 
						order by A.element_position ASC";
				$params = array($form_id,$datasource_element_id_array[0],$form_id,$datasource_element_id_array[0]);

				$sth = mf_do_query($query,$params,$dbh);
				$matrix_rows_title = array();
				while($row = mf_do_fetch_result($sth)){
					if(strlen($row['element_title']) > 25){
						$row['element_title'] = substr($row['element_title'],0,25)."...";
					}

					$matrix_rows_title[] = $row['element_title'];
				}

				$main_series_array = array();
				$i=0;
				foreach ($data_series_array_allrows as $value) {
					$data_object = new stdClass();
					$data_object->name = $matrix_rows_title[$i];
					$data_object->data = $value;

					$main_series_array[] = $data_object;
					$i++;
				}
				$main_series_json = json_encode($main_series_array);
				$main_series_json = "series: {$main_series_json}";
			}else{
				$main_series_json = "series: [{ color: \"{$chart_bar_color}\", data: {$data_series_json} }]";
			}
			

			//set chart specific properties
			$line_series_defaults = '';
			$area_series_defaults = '';

			if($chart_type == 'line'){
				$line_series_defaults = "style: \"{$chart_line_style}\",";
			}else if($chart_type == 'area'){
				$area_series_defaults = "area: { line: { style: \"{$chart_line_style}\" } },";
			}

			//set properties for chart using date axis
			//date axis only supported for line and area chart
			$series_category_field = 'category';
			$series_aggregate = '';
			$category_axis_base_unit = '';

			if($chart_using_date_axis){
				$series_category_field = 'date'; 

				if(!empty($chart_date_axis_baseunit)){
					$category_axis_base_unit = "baseUnit: \"{$chart_date_axis_baseunit}s\",";
				}

				$series_aggregate = 'aggregate: "sum",';
			}

			$chart_init_code =<<<EOT
						theme: "{$chart_theme}",
						renderAs: "{$chart_rendering_engine}",
		                "title": {
		                	visible: {$chart_title_visible},
		                    position: "{$chart_title_position}",
		                    align: "{$chart_title_align}",
		                    text: "{$chart_title}"
		                },
		                {$chart_area_json}
		                legend: {
		                    visible: {$chart_legend_visible},
		                    position: "{$chart_legend_position}" 
		                },
		                seriesDefaults: {
		                    type: "{$chart_series_type}",
		                    categoryField: "{$series_category_field}",
		                    {$series_aggregate}
		                    {$line_series_defaults}
		                    {$area_series_defaults}
		                    stack: {$chart_is_stacked},
		                    labels: {
		                        visible: {$chart_labels_visible},
		                        template: "{$chart_labels_template}",
		                        align: "{$chart_labels_align}",
		                        position: "{$chart_labels_position}"
		                    }
		                },
		                {$main_series_json},
		                tooltip: {
		                    visible: {$chart_tooltip_visible},
		                    template: "{$chart_tooltip_template}"
		                },
		                valueAxis: {
		                    line: {
		                        visible: true
		                    },
		                    minorGridLines: {
		                        visible: {$chart_gridlines_visible}
		                    },
		                    majorGridLines: {
		                        visible: {$chart_gridlines_visible}
		                    }
		                },
		                categoryAxis: {
		                	{$category_axis_base_unit}
		                    line: {
		                        visible: true
		                    },
		                    majorGridLines: {
		                        visible: {$chart_gridlines_visible}
		                    },
		                    minorGridLines: {
		                        visible: {$chart_gridlines_visible}
		                    }
		                }
EOT;

		}

		//build the complete chart markup
		$chart_markup =<<<EOT
			<div class="chart-wrapper">
		        <div id="chart"></div>
		    </div>
		    <script>
		        function createChart() {
		            $("#chart").kendoChart({
		                {$chart_init_code}
		            });
		        }

		        $(document).ready(createChart);

		        $(window).on("resize", function() {
			      kendo.resize($(".chart-wrapper"));
			    });
		    </script>
EOT;
		
		return $chart_markup;

 	}

 	//get the currency symbol of an element
 	function mf_get_element_currency($dbh,$form_id,$element_name){
 		$exploded = explode('_', $element_name);
 		$element_id = (int) $exploded[1];
 		
 		//get the currency of the payment amount
 		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_currency'));
		$payment_currency = strtoupper($form_properties['payment_currency']);

		//get the currency of the price field
		$query = "select element_constraint from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";		
		$params = array($form_id,$element_id);
		
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$element_constraint = $row['element_constraint'];

		if(empty($element_constraint)){
			$element_constraint = 'dollar';
		}

		switch ($element_constraint){
			case 'dollar' : $currency = '&#36;';break;	
			case 'pound'  : $currency = '&#163;';break;
			case 'euro'   : $currency = '&#8364;';break;
			case 'yen' 	  : $currency = '&#165;';break;
			case 'baht'   : $currency = '&#3647;';break;
			case 'forint' : $currency = '&#70;&#116;';break;
			case 'franc'  : $currency = 'CHF';break;
			case 'koruna' : $currency = '&#75;&#269;';break;
			case 'krona'  : $currency = 'kr';break;
			case 'pesos'  : $currency = '&#36;';break;
			case 'rand'   : $currency = 'R';break;
			case 'ringgit' : $currency = 'RM';break;
			case 'reais'   : $currency = 'R&#36;';break;
			case 'rupees' : $currency = 'Rs';break;
			case 'zloty'  : $currency = '&#122;&#322;';break;
			case 'riyals' : $currency = '&#65020;';break;
		}

		//if the column name is "payment_amount", this column is coming from ap_form_payments table
		//in this case, we need to use the currency  setting from the ap_forms table
		if($element_name == 'payment_amount'){
			switch ($payment_currency) {
				case 'USD' : $currency = '&#36;';break;
				case 'EUR' : $currency = '&#8364;';break;
				case 'GBP' : $currency = '&#163;';break;
				case 'AUD' : $currency = '&#36;';break;
				case 'CAD' : $currency = '&#36;';break;
				case 'JPY' : $currency = '&#165;';break;
				case 'THB' : $currency = '&#3647;';break;
				case 'HUF' : $currency = '&#70;&#116;';break;
				case 'CHF' : $currency = 'CHF';break;
				case 'CZK' : $currency = '&#75;&#269;';break;
				case 'SEK' : $currency = 'kr';break;
				case 'DKK' : $currency = 'kr';break;
				case 'NOK' : $currency = 'kr';break;
				case 'PHP' : $currency = '&#36;';break;
				case 'IDR' : $currency = 'Rp';break;
				case 'INR' : $currency = 'Rs';break;
				case 'MYR' : $currency = 'RM';break;
				case 'PLN' : $currency = '&#122;&#322;';break;
				case 'BRL' : $currency = 'R&#36;';break;
				case 'HKD' : $currency = '&#36;';break;
				case 'MXN' : $currency = 'Mex&#36;';break;
				case 'TWD' : $currency = 'NT&#36;';break;
				case 'TRY' : $currency = 'TL';break;
				case 'NZD' : $currency = '&#36;';break;
				case 'SGD' : $currency = '&#36;';break;
				default: $currency = ''; break;
			}
		}

		return $currency;
 	}

 	//Generate markup for Grid
 	function mf_display_grid($dbh,$chart_access_key){

 		//when the total entries of a form exceed this threshold, the grid will use server side paging/sorting
 		//this mean slower operation with the gid on the client side
 		$max_entries_threshold = 1000;

 		//the maximum amount of column that will be displayed by the grid for the first time
 		$init_max_columns = 1000;

 		//get widget property
 		$query = "SELECT 
						form_id,
						chart_id,
						chart_enable_filter,
						chart_filter_type,
						chart_height,
						chart_theme,
						chart_grid_page_size,
						chart_title,
						chart_title_position 
				    FROM
				    	".MF_TABLE_PREFIX."report_elements
				   WHERE
				   		access_key = ?";
		$params = array($chart_access_key);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$form_id  				= (int) $row['form_id'];
		$chart_id 				= (int) $row['chart_id'];
		
		$chart_enable_filter 	= (int) $row['chart_enable_filter'];
		$chart_filter_type		= $row['chart_filter_type'];
		$chart_height 			= (int) $row['chart_height'];
		$chart_theme 			= $row['chart_theme'];
		$chart_grid_page_size 	= (int) $row['chart_grid_page_size'];

		$chart_title 		  = htmlspecialchars($row['chart_title'],ENT_QUOTES);
		$chart_title_position = htmlspecialchars($row['chart_title_position'],ENT_QUOTES);

		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant','form_approval_enable'));

		$chart_grid_page_sizes = $chart_grid_page_size.','.($chart_grid_page_size * 2).','.($chart_grid_page_size * 3);

		//determine serverPaging and serverSorting functionality
		$query = "select count(*) total_entries from ".MF_TABLE_PREFIX."form_{$form_id} where `status`=1";
		$params = array();

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
		$total_entries = $row['total_entries'];

		//if a form has more than $max_entries_threshold (default 1000 entries), enable server side paging/sorting
		//otherwise, load all the data into the browser
		if($total_entries < $max_entries_threshold){
			$server_paging_status = 'false';
			$server_sorting_status = 'false';
			$groupable_status = 'true';
		}else{
			$server_paging_status = 'true';
			$server_sorting_status = 'true';
			$groupable_status = 'false';
		}

		//get selected columns title
		$columns_meta  = mf_get_columns_meta($dbh,$form_id);
		$columns_label = $columns_meta['name_lookup'];
		$columns_type  = $columns_meta['type_lookup'];

		//remove signature fields first, so that it won't get displayed on the grid
		foreach ($columns_type as $element_name => $element_type) {
			if($element_type == 'signature'){
				unset($columns_label[$element_name]);
			}
		}

		if($form_properties['payment_enable_merchant'] == 1){
			//if merchant is enable, we need to add payment columns after the "ip_address"
			//so that they are all ordered correctly, same as display entries
			
			$payment_columns_label = array();
			$payment_columns_label['payment_amount'] = 'Payment Amount';
			$payment_columns_label['payment_status'] = 'Payment Status';
			$payment_columns_label['payment_id']	 = 'Payment ID';
			mf_array_insert($columns_label,4,$payment_columns_label);
			
		}

		if($form_properties['form_approval_enable'] == 1){
			$columns_label['approval_status'] = 'Approval Status';
		}

		//get current grid column preference
		$query = "select element_name from ".MF_TABLE_PREFIX."grid_columns where form_id=? and chart_id=? order by position asc";
		$params = array($form_id,$chart_id);

		$sth = mf_do_query($query,$params,$dbh);
		while($row = mf_do_fetch_result($sth)){
			$element_name = $row['element_name'];
			$grid_column_preference[$element_name] = $columns_label[$element_name];
		}

		//if there is no grid prefernce, display the first 6 fields
		if(empty($grid_column_preference)){
			$temp_slice = array_slice($columns_label,0,9);
			unset($temp_slice['date_updated']);
			unset($temp_slice['ip_address']);
			$grid_column_preference = $temp_slice;
		}

		$column_data_array = array();
		$data_model_array = array();

		$i=0;
		foreach ($grid_column_preference as $element_name => $element_title) {
			$column_obj = new stdClass();
			$column_obj->field 	 = $element_name;
			$column_obj->title   = $element_title;
			$column_obj->encoded = false;

			if($i>$init_max_columns){
				$column_obj->hidden = true;
			}

			if($element_name == 'id'){
				$column_obj->width 	 = 55;
			}

			//if the field is date field, set the formatting
			if($columns_type[$element_name] == 'date'){
				if($element_name == 'date_created' || $element_name == 'date_updated'){
					$column_obj->format = '{0: d MMM yyyy hh:mm tt}';
				}else{ //this is mm/dd/yyyy
					$column_obj->format = '{0: MMM d, yyyy}';
				}
				
				$data_model_type = 'date';
			}else if($columns_type[$element_name] == 'europe_date'){ //this is dd/mm/yyyy
				$column_obj->format = '{0: d MMM yyyy}';
				$data_model_type = 'date';
			}else if($columns_type[$element_name] == 'number' || $columns_type[$element_name] == 'rating'){
				$data_model_type = 'number';
			}else if($columns_type[$element_name] == 'money'){
				$element_currency = mf_get_element_currency($dbh,$form_id,$element_name);
				$element_currency = str_replace('#', '\#', $element_currency);

				$column_obj->template = "<div class=\"me_right_div\">#= ($element_name == null) ? ' ' : '{$element_currency}' + {$element_name} #</div>";
				$data_model_type = 'number';
			}else{
				$data_model_type = 'string';
			}

			$column_data_array[] = $column_obj;

			$data_model_array[] = $element_name.': { type: "'.$data_model_type.'" }';

			$i++;
		}

		$column_data_json = json_encode($column_data_array);
		$data_model_joined = implode(",\n", $data_model_array);

		//build chart title markup
		$chart_title_top_markup = '';
		$chart_title_bottom_markup = '';

		if(!empty($chart_title)){
			if($chart_title_position == 'top'){
				$chart_title_top_markup    = "<h2 class=\"mf_grid_title\">{$chart_title}</h2>";
			}else{
				$chart_title_bottom_markup = "<h2 class=\"mf_grid_title\">{$chart_title}</h2>";
			}
		}

 		//build the complete grid markup
		$grid_markup =<<<EOT
			{$chart_title_top_markup}
			<div class="k-content">
		        <div id="mf_grid"></div>
		    </div>
		    {$chart_title_bottom_markup}

		    <script>
                $(document).ready(function () {
                	var dataModel = {
						fields: {
							{$data_model_joined}
						}
					};

                    var dataSource = new kendo.data.DataSource({
                      transport: {
                        read: {
                          url: "grid_datasource.php",
                          dataType: "jsonp",
                          data: {
                            key: "{$chart_access_key}" 
                          }
                        }
                      },
                      serverSorting: {$server_sorting_status},
                      serverPaging: {$server_paging_status},
                      schema: {
                        model: dataModel,
                        data: "rows_data", 
                        total: "rows_total"
                      },
                      
                      pageSize: {$chart_grid_page_size}
                    });

                    $("#mf_grid").kendoGrid({
                        dataSource: dataSource,
                        height: {$chart_height},
                        groupable: {$groupable_status},
                        sortable: true,
                        reorderable: true,
                        filterable: {
						    extra: false
						},
                        columnMenu: true,
                        resizable: true,
                        pageable: {
                            refresh: false,
                            pageSizes: [{$chart_grid_page_sizes}],
                            buttonCount: 5
                        },
                        columns: {$column_data_json}
                    });
                    

                    
                });
            </script>
EOT;

 		return $grid_markup;
 	}

 	//display rating scorecard widget
 	function mf_display_rating_scorecard($dbh,$chart_access_key){
 		
 		$mf_settings 	= mf_get_settings($dbh);
		$mf_version_tag = '?'.substr(md5($mf_settings['machform_version']),-6);

		$query = "SELECT 
						form_id,
						chart_id,
						chart_enable_filter,
						chart_datasource,
						chart_filter_type,
						chart_title,
						chart_title_align,
						chart_theme,
						chart_rating_size,
						chart_rating_bars_visible,
						chart_rating_total_visible 

				    FROM
				    	".MF_TABLE_PREFIX."report_elements
				   WHERE
				   		access_key = ?";
		$params = array($chart_access_key);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$form_id  				= (int) $row['form_id'];
		$chart_id 				= (int) $row['chart_id'];
		$chart_datasource 		= $row['chart_datasource']; 

		$chart_enable_filter 	= (int) $row['chart_enable_filter'];
		$chart_filter_type		= $row['chart_filter_type'];
		$chart_title 			= htmlspecialchars($row['chart_title'],ENT_QUOTES);
		$chart_title_align 		= $row['chart_title_align'];
		$chart_theme 			= $row['chart_theme'];

		$chart_rating_bars_visible	= (int) $row['chart_rating_bars_visible'];
		$chart_rating_total_visible = (int) $row['chart_rating_total_visible'];
		$chart_rating_size 			= $row['chart_rating_size']; //possible values: small - medium - large
		
		//get rating scorecard data
		$exploded = explode('_',$chart_datasource);
		$datasource_element_id = (int) $exploded[1];

		//get rating max
		$query = "SELECT element_rating_max FROM `".MF_TABLE_PREFIX."form_elements` WHERE form_id=? and element_id=? and element_status=1";
		$params = array($form_id,$datasource_element_id);
			
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$rating_max = $row['element_rating_max'];

		//get filter/where clause if enabled for this chart
		if(!empty($chart_enable_filter)){
			$chart_where_clause = mf_get_chart_filter($dbh,$form_id,$chart_id,$chart_filter_type);
		}

		//get total rating
		$total_rating = 0;

		if(!empty($chart_enable_filter)){
			$query = "SELECT 
							count(*) total_row 
						FROM 
							(SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` {$chart_where_clause}) A 
					   WHERE 
							A.`status`=1 and A.`element_{$datasource_element_id}` <> 0";
		}else{
			$query = "SELECT 
							count(*) total_row 
						FROM 
							`".MF_TABLE_PREFIX."form_{$form_id}` 
					   WHERE 
							`status`=1 and `element_{$datasource_element_id}` <> 0";
		}
		
		$params = array();
				
		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);
			
		$total_rating = $row['total_row'];

		$total_rating_markup = '';
		if(!empty($chart_rating_total_visible)){
			if(!empty($total_rating)){
				$total_rating_markup = number_format($row['total_row']).' ratings';
			}else{
				$total_rating_markup = '';
			}
		}

		//get rating average
		if(!empty($chart_enable_filter)){
			$query = "SELECT 
							ROUND(AVG(A.`$chart_datasource`),1) rating_average 
						FROM 
							(SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` {$chart_where_clause}) A 
					   WHERE 
					   		A.`status`=1 and A.`element_{$datasource_element_id}` <> 0";
		}else{
			$query = "SELECT 
							ROUND(AVG(`$chart_datasource`),1) rating_average 
						FROM 
							`".MF_TABLE_PREFIX."form_{$form_id}` 
					   WHERE 
					   		`status`=1 and `element_{$datasource_element_id}` <> 0";
		}
		
		$params = array();

		$sth = mf_do_query($query,$params,$dbh);
		$row = mf_do_fetch_result($sth);

		$rating_average = $row['rating_average'] ?? 0;
		$rating_average = str_replace('.0','',$rating_average);
		
		//construct the rating average star icons
		$rating_average_markup = '';
		for($i=1;$i<=$rating_max;$i++){
			
			if($i<=$rating_average){
				$rating_average_markup .= '<span class="icon-star-full2 mf rating-checked"></span>'."\n";
			}else if($i>$rating_average){
				if(($i - $rating_average) <= 0.5){
					$rating_average_markup .= '<span class="icon-star-half mf rating-checked"></span>'."\n";
				}else{
					$rating_average_markup .= '<span class="icon-star-empty3 mf rating-checked"></span>'."\n";
				}
			}
		}
		
		$rating_bar_markup = '';
		if(!empty($chart_rating_bars_visible)){
			
			//get rating data for each value
			if(!empty($chart_enable_filter)){
				$query = "SELECT 
								A.`element_{$datasource_element_id}` as rating_value,
								count(A.`element_{$datasource_element_id}`) as rating_total 
							FROM 
								(SELECT * FROM `".MF_TABLE_PREFIX."form_{$form_id}` {$chart_where_clause}) A 
						   WHERE 
						   		A.`status`=1 
						   	 AND 
						   	 	A.`element_{$datasource_element_id}` <> 0 
						GROUP BY 
								A.`element_{$datasource_element_id}`";
			}else{
				$query = "SELECT 
								`element_{$datasource_element_id}` as rating_value,
								count(`element_{$datasource_element_id}`) as rating_total 
							FROM 
								`".MF_TABLE_PREFIX."form_{$form_id}` 
						   WHERE 
						   		`status`=1 
						   	 AND 
						   	 	`element_{$datasource_element_id}` <> 0 
						GROUP BY 
								`element_{$datasource_element_id}`";
			}
			

			$params = array();
			$rating_data_lookup = array();

			$sth = mf_do_query($query,$params,$dbh);
			while($row = mf_do_fetch_result($sth)){
				$rating_data_lookup[$row['rating_value']]['total'] = $row['rating_total'];
				$rating_data_lookup[$row['rating_value']]['percentage'] = round(($row['rating_total'] / $total_rating) * 100);
			}

			if($rating_max <= 5){
				$rating_max_class = 'rating-max-5';
			}else{
				$rating_max_class = 'rating-max-10';
			}

			$rating_bar_details_markup = '';
			for($i=$rating_max;$i>=1;$i--){
				
				//star rating icons markup
				$rating_side_icon_markup = '';
				for($j=1;$j<=$i;$j++){
					$rating_side_icon_markup .= '<span class="icon-star-full2 mf rating-checked"></span>';
				}

				if($i == $rating_max){
					$first_row_class = 'first-row';
				}else{
					$first_row_class = '';
				}

				if(isset($rating_data_lookup[$i]['percentage'])){
					$bar_percentage_value = $rating_data_lookup[$i]['percentage'].'%';
				}else{
					$bar_percentage_value = '0%';
				}

				if(!empty($chart_rating_total_visible)){
					$bar_total_value = '('.$rating_data_lookup[$i]['total'].')';
				}

				$rating_bar_row_markup =<<<EOT
				<div class="rating-side {$first_row_class}">
				  <div>
				  	 {$rating_side_icon_markup}
				  </div>
				</div>
				<div class="rating-middle {$first_row_class}">
				   <div class="bar-container">
				      <div class="bar" style="width: {$bar_percentage_value}" title="{$bar_percentage_value} {$bar_total_value}"></div>
				   </div>
				</div>
				<div class="rating-side rating-right {$first_row_class}">
				  	<div>{$bar_percentage_value}</div>
				</div>
EOT;
				
				$rating_bar_details_markup .= $rating_bar_row_markup;
			}

			$rating_bar_markup = "<div class=\"rating-bar {$rating_max_class} row\">{$rating_bar_details_markup}</div>";

		}
		
		
		if(!empty($chart_title)){
			$hr_markup = '<hr>';
		}else{
			$hr_markup = '';
		}

 		//build the complete rating scorecard markup
		$rating_scorecard_markup =<<<EOT
<div class="rating-widget rating-{$chart_rating_size}">
	<span class="rating-heading rating-heading-{$chart_title_align}">{$chart_title}</span>
	{$hr_markup}
	<div class="rating-score {$rating_max_class}">
		<p  class="rating-total">{$total_rating_markup}</p>
		<h1 class="rating-score-value">{$rating_average}</h1>
		<p  class="rating-score-sublabel">out of {$rating_max}</p>
		{$rating_average_markup}
	</div>
	{$rating_bar_markup}
</div>
EOT;
		
		return $rating_scorecard_markup;
 	}

 	//return the "where" clause being used to filter a chart datasource
 	function mf_get_chart_filter($dbh,$form_id,$chart_id,$filter_type){

 		//get filter keywords from ap_report_filters table
 		$query = "select
						element_name,
						filter_condition,
						filter_keyword
					from 
						".MF_TABLE_PREFIX."report_filters
				   where
				   		form_id = ? and chart_id = ?  
				order by 
				   		arf_id asc";
		$params = array($form_id,$chart_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		$i = 0;
		$filter_data = array();
		while($row = mf_do_fetch_result($sth)){
			$filter_data[$i]['element_name'] 	 = $row['element_name'];
			$filter_data[$i]['filter_condition'] = $row['filter_condition'];
			$filter_data[$i]['filter_keyword'] 	 = $row['filter_keyword'];
			$i++;
		}

		//code below pretty much the same as within mf_display_entries_table()
		/********************************************************************************************/
		$form_properties = mf_get_form_properties($dbh,$form_id,array('payment_enable_merchant'));

		//prepare column header names lookup

		//get form element options first (checkboxes, choices, dropdown)
		$query = "select 
						element_id,
						option_id,
						`option`
					from 
						".MF_TABLE_PREFIX."element_options 
				   where 
				   		form_id=? and live=1 
				order by 
						element_id,position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option']),ENT_QUOTES);
		}

		//get element options for matrix fields
		$query = "select 
						A.element_id,
						A.option_id,
						(select if(B.element_matrix_parent_id=0,A.option,
							(select 
									C.`option` 
							   from 
							   		".MF_TABLE_PREFIX."element_options C 
							  where 
							  		C.element_id=B.element_matrix_parent_id and 
							  		C.form_id=A.form_id and 
							  		C.live=1 and 
							  		C.option_id=A.option_id))
						) 'option_label'
					from 
						".MF_TABLE_PREFIX."element_options A left join ".MF_TABLE_PREFIX."form_elements B on (A.element_id=B.element_id and A.form_id=B.form_id)
				   where 
				   		A.form_id=? and A.live=1 and B.element_type='matrix' and B.element_status=1
				order by 
						A.element_id,A.option_id asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$matrix_element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option_label']),ENT_QUOTES);
		}
		
		//get 'multiselect' status of matrix fields
		$query = "select 
						  A.element_id,
						  A.element_matrix_parent_id,
						  A.element_matrix_allow_multiselect,
						  (select if(A.element_matrix_parent_id=0,A.element_matrix_allow_multiselect,
						  			 (select B.element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements B where B.form_id=A.form_id and B.element_id=A.element_matrix_parent_id)
						  			)
						  ) 'multiselect' 
					  from 
					 	  ".MF_TABLE_PREFIX."form_elements A
					 where 
					 	  A.form_id=? and A.element_status=1 and A.element_type='matrix'";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$matrix_multiselect_status[$row['element_id']] = $row['multiselect'];
		}


		/******************************************************************************************/
		//set column properties for basic fields
		$column_name_lookup['date_created']   = 'Date Created';
		$column_name_lookup['date_updated']   = 'Date Updated';
		$column_name_lookup['ip_address'] 	  = 'IP Address';
		
		$column_type_lookup['id'] 			= 'number';
		$column_type_lookup['row_num']		= 'number';
		$column_type_lookup['date_created'] = 'date';
		$column_type_lookup['date_updated'] = 'date';
		$column_type_lookup['ip_address'] 	= 'text';
		

		if($form_properties['payment_enable_merchant'] == 1){
			$column_name_lookup['payment_amount'] = 'Payment Amount';
			$column_name_lookup['payment_status'] = 'Payment Status';
			$column_name_lookup['payment_id']	  = 'Payment ID';
			
			$column_type_lookup['payment_amount'] = 'money';
			$column_type_lookup['payment_status'] = 'payment_status';
			$column_type_lookup['payment_id']	  = 'text';
		}
		
		//get column properties for other fields
		$query  = "select 
						 element_id,
						 element_title,
						 element_type,
						 element_constraint,
						 element_choice_has_other,
						 element_choice_other_label,
						 element_time_showsecond,
						 element_time_24hour,
						 element_matrix_allow_multiselect,
						 ifnull(element_address_subfields_labels,'') element_address_subfields_labels    
				     from 
				         `".MF_TABLE_PREFIX."form_elements` 
				    where 
				    	 form_id=? and element_status=1 and element_type not in('section','page_break','media')
				 order by 
				 		 element_position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		$element_radio_has_other = array();

		while($row = mf_do_fetch_result($sth)){

			$element_type 	    = $row['element_type'];
			$element_constraint = $row['element_constraint'];
			

			//get 'other' field label for checkboxes and radio button
			if($element_type == 'checkbox' || $element_type == 'radio'){
				if(!empty($row['element_choice_has_other'])){
					$element_option_lookup[$row['element_id']]['other'] = htmlspecialchars(strip_tags($row['element_choice_other_label']),ENT_QUOTES);
				
					if($element_type == 'radio'){
						$element_radio_has_other['element_'.$row['element_id']] = true;	
					}
				}
			}

			$row['element_title'] = htmlspecialchars(strip_tags($row['element_title']),ENT_QUOTES);

			if('address' == $element_type){ //address has 6 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Street Address';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = 'Address Line 2';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = 'City';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = 'State/Province/Region';
				$column_name_lookup['element_'.$row['element_id'].'_5'] = 'Zip/Postal Code';
				$column_name_lookup['element_'.$row['element_id'].'_6'] = 'Country';
				
				//if there is custom label for address subfields, use it instead
				if(!empty($row['element_address_subfields_labels'])){
					$subfields_labels_obj = json_decode($row['element_address_subfields_labels']);
					
					if(!empty($subfields_labels_obj->street)){
						$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - '.$subfields_labels_obj->street;
					}
					if(!empty($subfields_labels_obj->street2)){
						$column_name_lookup['element_'.$row['element_id'].'_2'] = $subfields_labels_obj->street2;
					}
					if(!empty($subfields_labels_obj->city)){
						$column_name_lookup['element_'.$row['element_id'].'_3'] = $subfields_labels_obj->city;
					}
					if(!empty($subfields_labels_obj->state)){
						$column_name_lookup['element_'.$row['element_id'].'_4'] = $subfields_labels_obj->state;
					}
					if(!empty($subfields_labels_obj->postal)){
						$column_name_lookup['element_'.$row['element_id'].'_5'] = $subfields_labels_obj->postal; 
					}
					if(!empty($subfields_labels_obj->country)){
						$column_name_lookup['element_'.$row['element_id'].'_6'] = $subfields_labels_obj->country;
					}
				}

				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_5'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_6'] = $row['element_type'];
				
			}elseif ('simple_name' == $element_type){ //simple name has 2 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - Last';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				
			}elseif ('simple_name_wmiddle' == $element_type){ //simple name with middle has 3 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - Middle';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Last';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				
			}elseif ('name' == $element_type){ //name has 4 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Title';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Last';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = $row['element_title'].' - Suffix';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				
			}elseif ('name_wmiddle' == $element_type){ //name with middle has 5 fields
				$column_name_lookup['element_'.$row['element_id'].'_1'] = $row['element_title'].' - Title';
				$column_name_lookup['element_'.$row['element_id'].'_2'] = $row['element_title'].' - First';
				$column_name_lookup['element_'.$row['element_id'].'_3'] = $row['element_title'].' - Middle';
				$column_name_lookup['element_'.$row['element_id'].'_4'] = $row['element_title'].' - Last';
				$column_name_lookup['element_'.$row['element_id'].'_5'] = $row['element_title'].' - Suffix';
				
				$column_type_lookup['element_'.$row['element_id'].'_1'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_2'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_3'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_4'] = $row['element_type'];
				$column_type_lookup['element_'.$row['element_id'].'_5'] = $row['element_type'];
				
			}elseif('money' == $element_type){//money format
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
				if(!empty($element_constraint)){
					$column_type_lookup['element_'.$row['element_id']] = 'money_'.$element_constraint; //euro, pound, yen,etc
				}else{
					$column_type_lookup['element_'.$row['element_id']] = 'money_dollar'; //default is dollar
				}
			}elseif('checkbox' == $element_type){ //checkboxes, get childs elements
							
				$this_checkbox_options = $element_option_lookup[$row['element_id']];
				
				foreach ($this_checkbox_options as $option_id=>$option){
					$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
					$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = $row['element_type'];
				}
			}elseif ('time' == $element_type){
				
				if(!empty($row['element_time_showsecond']) && !empty($row['element_time_24hour'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time_24hour';
				}else if(!empty($row['element_time_showsecond'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time';
				}else if(!empty($row['element_time_24hour'])){
					$column_type_lookup['element_'.$row['element_id']] = 'time_24hour_noseconds';
				}else{
					$column_type_lookup['element_'.$row['element_id']] = 'time_noseconds';
				}
				
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
			}else if('matrix' == $element_type){ 
				
				if(empty($matrix_multiselect_status[$row['element_id']])){
					$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
					$column_type_lookup['element_'.$row['element_id']] = 'matrix_radio';
				}else{
					$this_checkbox_options = $matrix_element_option_lookup[$row['element_id']];
					
					foreach ($this_checkbox_options as $option_id=>$option){
						$option = $option.' - '.$row['element_title'];
						$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
						$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = 'matrix_checkbox';
					}
				}
			}else if('signature' == $element_type){
				//don't display signature field
				continue;
			}else{ //for other elements with only 1 field
				$column_name_lookup['element_'.$row['element_id']] = $row['element_title'];
				$column_type_lookup['element_'.$row['element_id']] = $row['element_type'];
			}

			
		}
		/******************************************************************************************/
		//if there is any radio fields which has 'other', we need to query that field as well
		if(!empty($element_radio_has_other)){
			$radio_has_other_array = array();
			foreach($element_radio_has_other as $element_name=>$value){
				$radio_has_other_array[] = $element_name.'_other';
			}
		}
		
		$display_incomplete_entries = false; //at this moment, the chart can only being used to display completed entries

		if($display_incomplete_entries === true){
			$incomplete_status = 1;
		}else{
			$incomplete_status = 0;
		}

		$payment_table_columns = array('payment_amount','payment_status','payment_id');

		//determine if the filter data contain payment fields or not
		$filter_has_payment_field = false;
		if(!empty($filter_data)){
			foreach ($filter_data as $value) {
				$element_name = $value['element_name'];
				if(in_array($element_name, $payment_table_columns)){
					$filter_has_payment_field = true;
					break;
				}
			}
		}

		//if the filter data refer to one of payment field them, we need to manually include the payment fields as preference
		if($filter_has_payment_field === true){
			$payment_columns_prefs = array('payment_amount','payment_status','payment_id');
		}

		if($display_incomplete_entries === true){
			//only display incomplete entries
			$status_clause = "`status`=2";
		}else{
			//only display completed entries
			$status_clause = "`status`=1";
		}

		if(!empty($filter_data)){

			if($filter_type == 'all'){
				$condition_type = ' AND ';
			}else{
				$condition_type = ' OR ';
			}

			$where_clause_array = array();

			foreach ($filter_data as $value) {
				$element_name 	  = $value['element_name'];
				$filter_condition = $value['filter_condition'];
				$filter_keyword   = addslashes($value['filter_keyword']);

				$filter_element_type = $column_type_lookup[$element_name];

				$temp = explode('_', $element_name);
				$element_id = $temp[1];

				//if the filter is a column from ap_form_payments table
				//we need to replace $element_name with the subquery to ap_form_payments table
				if(!empty($payment_columns_prefs) && in_array($element_name, $payment_table_columns)){
					if($element_name == 'payment_status'){
						$element_name = "ifnull((select 
													`{$element_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1),'unpaid')";
					}else{
						$element_name = "(select 
													`{$element_name}` 
												 from ".MF_TABLE_PREFIX."form_payments 
												where 
													 form_id='{$form_id}' and record_id=A.id  and `status` = 1 
											 order by 
											 		 afp_id desc limit 1)";
					}
				}
				
				
				if(in_array($filter_element_type, array('radio','select','matrix_radio'))){
					
					//these types need special steps to filter
					//we need to look into the ap_element_options first and do the filter there
					$null_clause = '';
					if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} = 0";
						}
					}

					//do a query to ap_element_options table
					$query = "select 
									option_id 
								from 
									".MF_TABLE_PREFIX."element_options 
							   where 
							   		form_id=? and
							   		element_id=? and
							   		live=1 and 
							   		`option` {$where_operand} {$where_keyword}";
					
					$params = array($form_id,$element_id);
			
					$filtered_option_id_array = array();
					$sth = mf_do_query($query,$params,$dbh);
					while($row = mf_do_fetch_result($sth)){
						$filtered_option_id_array[] = $row['option_id'];
					}

					$filtered_option_id = implode("','", $filtered_option_id_array);

					$filter_radio_has_other = false;
					if($filter_element_type == 'radio' && !empty($radio_has_other_array)){
						if(in_array($element_name.'_other', $radio_has_other_array)){
							$filter_radio_has_other = true;
						}else{
							$filter_radio_has_other = false;
						}
					}
					
					if($filter_radio_has_other){ //if the filter is radio button field with 'other'
						if(!empty($filtered_option_id_array)){
							$where_clause_array[] = "({$element_name} IN('{$filtered_option_id}') OR {$element_name}_other {$where_operand} {$where_keyword} {$null_clause})"; 
						}else{
							$where_clause_array[] = "({$element_name}_other {$where_operand} {$where_keyword} {$null_clause})";
						}
					}else{//otherwise, for the rest of the field types
						if(!empty($filtered_option_id_array)){							
							if(!empty($null_clause)){
								$where_clause_array[] = "({$element_name} IN('{$filtered_option_id}') {$null_clause})";
							}else{
								$where_clause_array[] = "{$element_name} IN('{$filtered_option_id}')";
							} 
						}else{
							if(!empty($null_clause)){
								$where_clause_array[] = str_replace("OR", '', $null_clause);
							}
						}
					}
				}else if(in_array($filter_element_type, array('date','europe_date'))){

					//if fixed date format is being used as the keyword
					if(strpos($filter_keyword,'/') !== false){
						$date_exploded = array();
						$date_exploded = explode('/', $filter_keyword); //the filter_keyword has format mm/dd/yyyy
						
						$filter_keyword = $date_exploded[2].'-'.$date_exploded[0].'-'.$date_exploded[1];
					}else{
						//if relative-date format is being used, we need to convert it first
						$filter_keyword = date("Y-m-d",strtotime($filter_keyword));
					}

					if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
					}

					$where_clause_array[] = "date({$element_name}) {$where_operand} {$where_keyword}"; 
				}else{
					$null_clause = '';

					if($filter_condition == 'is'){
						$where_operand = '=';
						$where_keyword = "'{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'is_not'){
						$where_operand = '<>';
						$where_keyword = "'{$filter_keyword}'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'begins_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'ends_with'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'contains'){
						$where_operand = 'LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'not_contain'){
						$where_operand = 'NOT LIKE';
						$where_keyword = "'%{$filter_keyword}%'";

						if(!empty($filter_keyword)){
							$null_clause = "OR {$element_name} IS NULL";
						}
					}else if($filter_condition == 'less_than' || $filter_condition == 'is_before'){
						$where_operand = '<';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'greater_than' || $filter_condition == 'is_after'){
						$where_operand = '>';
						$where_keyword = "'{$filter_keyword}'";
					}else if($filter_condition == 'is_one'){
						$where_operand = '=';
						$where_keyword = "'1'";
					}else if($filter_condition == 'is_zero'){
						$where_operand = '=';
						$where_keyword = "'0'";
					}
		 			
		 			if(!empty($null_clause)){
		 				$where_clause_array[] = "({$element_name} {$where_operand} {$where_keyword} {$null_clause})";
		 			}else{
		 				$where_clause_array[] = "{$element_name} {$where_operand} {$where_keyword}"; 
		 			}
					
				}
			}
			
			$where_clause = implode($condition_type, $where_clause_array);
			
			if(empty($where_clause)){
				$where_clause = "WHERE {$status_clause}";
			}else{
				$where_clause = "WHERE ({$where_clause}) AND {$status_clause}";
			}
			
						
		}

		return $where_clause;

 	}

 	//generate <option>..</option> markup that contain all chart-enabled fields (choice, dropdown, rating, checkbox, matrix) labels
 	function mf_get_chart_datasource_markup($dbh,$form_id,$params){

 		$form_id = (int) $form_id;

 		//if set to 'true', each option will be displayed, including each option on every line of the matrix
 		//if set to 'false' only the main label of the field (or each row of matrix) will be displayed
 		$show_expanded_options = $params['show_expanded_options'] ?? false; 

 		//if set to 'true', this function will only return rating fields
 		$show_rating_only = $params['show_rating_only'] ?? false;

 		$option_markup = '';

 		//if this is to get rating fields only, do it here and then exit
 		if($show_rating_only){
 			$query = "select 
							element_id,
							element_title
						from 
							".MF_TABLE_PREFIX."form_elements 
					   where 
					   		form_id=? and element_status=1 and element_type='rating'
					order by 
							element_position asc";
			$params = array($form_id);
			$sth = mf_do_query($query,$params,$dbh);
			
			while($row = mf_do_fetch_result($sth)){
				$row['element_title'] = htmlspecialchars($row['element_title'],ENT_QUOTES);

				$option_markup .= "<option value=\"element_{$row['element_id']}\">{$row['element_title']}</option>\n";
			}

			return $option_markup;
 		}

		//get form element options first (checkboxes, choices, dropdown)
		$query = "select 
						element_id,
						option_id,
						`option`
					from 
						".MF_TABLE_PREFIX."element_options 
				   where 
				   		form_id=? and live=1 
				order by 
						element_id,position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option']),ENT_QUOTES);
		}

		//get element options for matrix fields
		$query = "select 
						A.element_id,
						A.option_id,
						(select if(B.element_matrix_parent_id=0,A.option,
							(select 
									C.`option` 
							   from 
							   		".MF_TABLE_PREFIX."element_options C 
							  where 
							  		C.element_id=B.element_matrix_parent_id and 
							  		C.form_id=A.form_id and 
							  		C.live=1 and 
							  		C.option_id=A.option_id))
						) 'option_label'
					from 
						".MF_TABLE_PREFIX."element_options A left join ".MF_TABLE_PREFIX."form_elements B on (A.element_id=B.element_id and A.form_id=B.form_id)
				   where 
				   		A.form_id=? and A.live=1 and B.element_status=1 and B.element_type='matrix' 
				order by 
						A.element_id,A.option_id asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$element_id = $row['element_id'];
			$option_id  = $row['option_id'];
			
			$matrix_element_option_lookup[$element_id][$option_id] = htmlspecialchars(strip_tags($row['option_label']),ENT_QUOTES);
		}

		//get 'multiselect' status of matrix fields
		$query = "select 
						  A.element_id,
						  A.element_matrix_parent_id,
						  A.element_matrix_allow_multiselect,
						  (select if(A.element_matrix_parent_id=0,A.element_matrix_allow_multiselect,
						  			 (select B.element_matrix_allow_multiselect from ".MF_TABLE_PREFIX."form_elements B where B.form_id=A.form_id and B.element_id=A.element_matrix_parent_id)
						  			)
						  ) 'multiselect' 
					  from 
					 	  ".MF_TABLE_PREFIX."form_elements A
					 where 
					 	  A.form_id=? and A.element_status=1 and A.element_type='matrix'";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		
		while($row = mf_do_fetch_result($sth)){
			$matrix_multiselect_status[$row['element_id']] = $row['multiselect'];
		}

		$query  = "select 
						 element_id,
						 element_title,
						 element_guidelines,
						 element_type,
						 element_constraint,
						 element_matrix_parent_id,
						 element_choice_has_other,
						 element_choice_other_label,
						 element_time_showsecond,
						 element_time_24hour,
						 element_matrix_allow_multiselect,
						 element_rating_max    
				     from 
				         `".MF_TABLE_PREFIX."form_elements` 
				    where 
				    	 form_id=? and element_status=1 and element_type in('radio','select','checkbox','matrix','rating')
				 order by 
				 		 element_position asc";
		$params = array($form_id);
		$sth = mf_do_query($query,$params,$dbh);
		

		while($row = mf_do_fetch_result($sth)){

			$element_type 	    = $row['element_type'];
			$element_constraint = $row['element_constraint'];
			$element_rating_max = (int) $row['element_rating_max'];

			//get 'other' field label for checkboxes and radio button
			if($element_type == 'checkbox' || $element_type == 'radio'){
				if(!empty($row['element_choice_has_other'])){
					$element_option_lookup[$row['element_id']]['other'] = htmlspecialchars(strip_tags($row['element_choice_other_label']),ENT_QUOTES);
				
					if($element_type == 'radio'){
						//$element_radio_has_other['element_'.$row['element_id']] = true;	
					}
				}
			}

			if($element_type == 'rating'){
				$element_option_lookup = array();
				for($i=1;$i<=$element_rating_max;$i++){
					$option_label = $i;
					$option_id    = $i;
						
					$element_option_lookup[$row['element_id']][$option_id] = $option_label;
				}
			}

			$row['element_title'] 	   = htmlspecialchars(strip_tags($row['element_title']),ENT_QUOTES);
			$row['element_guidelines'] = htmlspecialchars(strip_tags($row['element_guidelines']),ENT_QUOTES);

			if(in_array($element_type, array('checkbox','select','radio','rating'))){ 	
				if($show_expanded_options){
					$this_checkbox_options = $element_option_lookup[$row['element_id']];
					
					$option_markup .= "<optgroup label=\"{$row['element_title']}\">\n";
					foreach ($this_checkbox_options as $option_id=>$option){
						$column_name_lookup['element_'.$row['element_id'].'_'.$option_id] = $option;
						$column_type_lookup['element_'.$row['element_id'].'_'.$option_id] = $row['element_type'];

						$option_markup .= "<option value=\"element_{$row['element_id']}_{$option_id}\">{$option}</option>\n";
					}
					$option_markup .= "</optgroup>\n";

				}else{
					$option_markup .= "<option value=\"element_{$row['element_id']}\">{$row['element_title']}</option>\n";
				}
			}else if('matrix' == $element_type){ 
				if($show_expanded_options){
					$this_row_options = $matrix_element_option_lookup[$row['element_id']];
					
					foreach ($this_row_options as $option_id=>$option){
						$option = $row['element_title'].' - '.$option;
						$option_markup .= "<option value=\"element_{$row['element_id']}_{$option_id}\">{$option}</option>\n";
					}
				}else{
					
					if(empty($row['element_matrix_parent_id'])){ //if this is the first row of the matrix
						$option_markup .= "<option value=\"element_{$row['element_id']}_allrows\">{$row['element_guidelines']} (All rows)</option>\n";
					}						
					$option_markup .= "<option value=\"element_{$row['element_id']}\">{$row['element_title']}</option>\n";

				} //end else show_expanded options
			}
			
		} //end while

		return $option_markup;

 	}

 ?>