<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MachForm Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="index, nofollow" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $mf_version_tag; ?>" media="screen" />
<link rel="stylesheet" type="text/css" href="css/main.mobile.css<?php echo $mf_version_tag; ?>" media="screen" />    
<link rel="stylesheet" type="text/css" href="css/bb_buttons.css<?php echo $mf_version_tag; ?>" media="screen" /> 
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">

<?php if(!empty($header_data)){ echo $header_data; } ?>
<link rel="stylesheet" type="text/css" href="css/override.css<?php echo $mf_version_tag; ?>" media="screen" /> 

<?php
	$active_admin_theme = $mf_settings['admin_theme'];
	if(!empty($_SESSION['mf_user_admin_theme'])){
		$active_admin_theme = $_SESSION['mf_user_admin_theme'];
	}

	$current_nav_tab = $current_nav_tab ?? 'manage_forms';
	
	if(!empty($active_admin_theme)){
		if($current_nav_tab == 'edit_theme' && $active_admin_theme == 'dark'){
			//speficially for dark theme on the theme editor, don't use the full dark theme CSS file
			//since it will overwrite the forms styling as well
			echo '<link href="css/themes/theme_dark_edit_theme.css'.$mf_version_tag.'" rel="stylesheet" type="text/css" />';
		}else{
			echo '<link href="css/themes/theme_'.$active_admin_theme.'.css'.$mf_version_tag.'" rel="stylesheet" type="text/css" />';
		}
	}else{
		echo '<link href="css/themes/theme_vibrant.css'.$mf_version_tag.'" rel="stylesheet" type="text/css" />';
	}
?>

</head>

<body>

<div id="bg">
<div id="header">
	<?php
		if(!empty($mf_settings['admin_image_url'])){
			$machform_logo_main = htmlentities($mf_settings['admin_image_url']);
		}else{
			$machform_logo_main = "images/machform_logo.png?{$mf_version_tag}";
			$logo_width_attr = 'width="140"';
		}
	?>

	<div id="header_content">
			<div id="logo">
				<span class="logo_helper"></span>
				<a href="manage_forms.php"><img src="<?php echo $machform_logo_main; ?>" <?php echo $logo_width_attr; ?> /></a>
			</div>	
			
			<div id="header_primary">
				<div class="nav_manage_forms <?php if($current_nav_tab == 'manage_forms'){ echo 'current_page_item'; } ?>">
					<a href="manage_forms.php"><span class="icon-document2"></span> <h6>Forms</h6></a>
				</div>

				<?php if(!empty($_SESSION['mf_user_privileges']['priv_new_themes'])){ ?>
				<div class="nav_themes <?php if($current_nav_tab == 'edit_theme'){ echo 'current_page_item'; } ?>">
					<a href="edit_theme.php" title="Edit Themes"><span class="icon-magic-wand"></span> <h6>Themes</h6></a>
				</div>
				<?php } ?>

				<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){ ?>
				<div class="nav_users <?php if($current_nav_tab == 'users'){ echo 'current_page_item'; } ?>">
					<a href="manage_users.php" title="Users"><span class="icon-user1"></span> <h6>Users</h6></a>
				</div>
				<div class="nav_settings <?php if($current_nav_tab == 'main_settings'){ echo 'current_page_item'; } ?>">
					<a href="main_settings.php" title="Settings"><span class="icon-cog"></span> <h6>Settings</h6></a>
				</div>
				<?php } ?>
				
				<div class="nav_account <?php if($current_nav_tab == 'my_account'){ echo 'current_page_item'; } ?>">
					<a href="my_account.php" title="My Account"><span class="icon-briefcase"></span> <h6>Account</h6></a>
				</div>
			</div>

			<div id="header_secondary">
				<div class="nav_logout">
					<a href="logout.php" title="Sign Out"><span class="icon-exit1"></span> <h6>Sign Out</h6></a>
				</div>

				<?php if(!empty($_SESSION['mf_user_privileges']['priv_administer'])){ ?>
				<div class="nav_help">
					<a href="https://docs.machform.com" target="_blank" title="Help"><span class="icon-bubble-question"></span> <h6>Help</h6></a>
				</div>
				<?php } ?>
				
				<?php if($mf_settings['customer_name'] == 'unregistered'){ ?>
				<div id="unregistered_license">
					<h6>UNREGISTERED LICENSE</h6>
				</div>
				<?php } ?>

			</div>
	</div>
</div><!-- /#header -->
<div id="container">
	<div id="main">
	