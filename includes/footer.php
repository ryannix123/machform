<div class="clear"></div>
	
	</div><!-- /#main -->
	
	<div id="footer">
		
		<p class="copyright">Copyright &copy; <a href="https://www.machform.com">Appnitro Software</a> 2007-2022</p>
		<div class="clear"></div>
		
	</div><!-- /#footer -->
	

</div><!-- /#container -->

</div><!-- /#bg -->

<?php
	if(!isset($disable_jquery_loading)){
		$disable_jquery_loading = false;
	}
	
	if($disable_jquery_loading !== true){
		echo '<script type="text/javascript" src="js/jquery.legacy.min.js'.$mf_version_tag.'"></script>';
	}
?>

<?php if(!empty($footer_data)){ echo $footer_data; } ?>
</body>
</html>