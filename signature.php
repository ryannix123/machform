<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/	
	require('includes/init.php');
	
	require('config.php');
	require('includes/db-core.php');
	require('includes/helper-functions.php');
	require('lib/signature-to-image.php');
	
	//get query string and parse it, query string is base64 encoded
	$query_string = trim($_GET['q']);
	parse_str(base64_decode($query_string),$params);
	
	$form_id 	= (int) $params['form_id'];
	$id      	= (int) $params['id'];
	$review     = (int) $params['review']; //if this is not empty, use review table to look for the signature data
	$field_name = preg_replace("/\W/", '', $params['el']);
	$signature_hash  = $params['hash'];
	
	
	if(empty($form_id) || empty($id) || empty($field_name) || empty($signature_hash)){
		die("Error. Incorrect URL.");
	}

	$dbh = mf_connect_db();

	if(!empty($review)){
		$query 	= "select `{$field_name}` from `".MF_TABLE_PREFIX."form_{$form_id}_review` where id=?";
	}else{
		$query 	= "select `{$field_name}` from `".MF_TABLE_PREFIX."form_{$form_id}` where id=?";
	}
	
	$params = array($id);

	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);
	$signature_data = $row[$field_name];

	if($signature_hash != md5($signature_data)){
		die("Error. Incorrect Signature URL.");
	}

	//determine signature type
	//there are 3 possibilities of signature type:
	//1. the old version, using json format, enclosed with [{ ... }]
	//2. data url format, starting with data:image/png
	//3. simple plain text, need to be rendered to image first

	if(substr($signature_data, 0,14) == 'data:image/png'){
		$signature_type = 'image';
	}else if(substr($signature_data, 0,2) == '[{'){
		$signature_type = 'json';
	}else{
		$signature_type = 'text';
	}

	//get signature height
	$exploded = explode('_', $field_name);
	$element_id = (int) $exploded[1];

	$query  = "select element_size from ".MF_TABLE_PREFIX."form_elements where form_id = ? and element_id = ?";
	$params = array($form_id,$element_id);
	
	$sth = mf_do_query($query,$params,$dbh);
	$row = mf_do_fetch_result($sth);

	$element_size = $row['element_size'];
	
	if($signature_type == 'image'){
		if($element_size == 'small'){
			$signature_height = 200;		
		}else if($element_size == 'medium'){
			$signature_height = 300;
		}else{
			$signature_height = 600;
		}

		header('Content-Type: image/png');
	
		//$signature_data started with 'data:image/png;base64,' and we need to remove it
		echo base64_decode(substr($signature_data,22));

	}else if($signature_type == 'json'){
		//the height from the older json signature is smaller
		//the older signature also didn't recognize screen with high DPI (i.e. retina display)
		if($element_size == 'small'){
			$signature_height = 70;		
		}else if($element_size == 'medium'){
			$signature_height = 130;
		}else{
			$signature_height = 260;
		}
		
		$signature_width = 309;

		$signature_options['imageSize'] = array($signature_width,$signature_height);
		$signature_options['penColour'] = array(0x00, 0x00, 0x00);
		$signature_img = sigJsonToImage($signature_data,$signature_options);

		//Output to browser
		header('Content-Type: image/png');
		imagepng($signature_img);

		//Destroy the image in memory when complete
		imagedestroy($signature_img);
	}else{

		//the code here is pretty much the same as signature_img_renderer.php
		$signature_text = trim($signature_data);

		if(empty($signature_text)){
			$signature_text = ' ';
		}

		//limit the text to 75 characters
		$signature_text = substr($signature_text,0,75);

		$uppercase_char_width = 60;
		$lowercase_char_width = 40;

		$image_height  = 150;
		$image_padding = 20;

		$total_char_count 	  = strlen($signature_text);
		$uppercase_char_count = strlen(preg_replace('![^A-Z]+!', '', $signature_text));
		$lowercase_char_count = $total_char_count - $uppercase_char_count;

		$image_width = ($uppercase_char_count * $uppercase_char_width) + 
					   ($lowercase_char_count * $lowercase_char_width) +
					   $image_padding;

		//create the image
		header('Content-Type: image/png');
		$im = imagecreatetruecolor($image_width, $image_height);


		//create some colors and fill the image with white background
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		
		imagefilledrectangle($im, 0, 0, $image_width-1, $image_height-1, $white);

		//load the signature font
		$font = __DIR__.'/css/fonts/mistral.ttf';

		//add the text
		$font_size = 80;
		imagettftext($im, $font_size, 0, 20, 100, $black, $font, $signature_text);

		//Using imagepng() results in clearer text compared with imagejpeg()
		imagepng($im);
		imagedestroy($im);
	}

	
?>