<?php
/********************************************************************************
 MachForm
  
 Copyright 2007-2016 Appnitro Software. This code cannot be redistributed without
 permission from http://www.appnitro.com/
 
 More info at: http://www.appnitro.com/
 ********************************************************************************/
	require('includes/init.php');

	//calculate the image width, based on the characters length
	$signature_text = isset($_REQUEST['signature_text']) ? trim($_REQUEST['signature_text']) : ' ';

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

?>