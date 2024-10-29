<?php
$error=false;
$label=array("AndroCaptcha_arrow_color"=>"Arrow Color","AndroCaptcha_line_color"=>"Line Color","AndroCaptcha_inner_circle_color"=>"Inner Circle Color","AndroCaptcha_outer_circle_color"=>"Outer Circle Color","AndroCaptcha_init_circle_color"=>"Initial Circle Color","AndroCaptcha_bg_color"=>"Background Color");
$current_user = wp_get_current_user();
$id=$current_user->ID;
	if(!empty($_POST))
	{
		$error_list="<div style='background-color:#ffebe8; border:solid 1px rgb(206, 0, 0); margin:20px; padding:10px;'>";
		foreach ($_POST as $key => $value)
		{
			if(empty($value))
			{
				$error_list.="<strong>ERROR:</strong> $label[$key] cannot be empty!<br>";
				$error=true;
				continue;
			}
			$result=preg_match('/^rgb\([0-9]{1,3}\,[0-9]{1,3}\,[0-9]{1,3}\)$|^rgba\([0-9]{1,3}\,[0-9]{1,3}\,[0-9]{1,3}\,[0-1]\.*[0-9]*\)$/i', $value,$matches);
			if($result!=1)	//to check if the submitted color is a valid rgb/rgba color
			{
				$error_list.="<strong>ERROR:</strong> There is something wrong with $label[$key]<br>";
				$error=true;
			}
		}
		$error_list.="</div>";
		if($error===true)
			echo $error_list;
		if($error===false && current_user_can( 'manage_options' ))	//options can be added with permissions and if there is no error!
		{
			$options=array();
			foreach ($_POST as $key => $value) {
				$options[$key]=$value;
			}
			 if(update_option('AndroCaptcha_options', $options ))
			 	echo "<div class='updated' style='margin:20px; padding:10px;' >Options Updated Successfully!</div>";
			 else
			 	echo "<div style='background-color:#ffebe8; border:solid 1px rgb(206, 0, 0); margin:20px; padding:10px;'>Sorry!!! There was something wrong in updating the options</div>";
		}
		show_options_page();
	}
	else
	{
	if ( 0 == $id) {
	    // Not logged in.
	} 
	else {
		
			show_options_page();
		}

	}

	function show_options_page()
	{
    	echo '
    	<h2>Android Captcha Options Menu</h2>
		<form action="" id="form1" method="POST" >
			<div id="AndroCaptcha_container"  >
			<h2>Example Captcha:</h2>
			<canvas width="300" height="300" id="AndroCaptcha_can" >
			</canvas>';
			include_once('AndroCaptcha.php');
			echo '<script>
			$j = jQuery.noConflict();
			var ocap=[7,5,3,6];				//just a sample array
			jQuery(document).ready(function(){
				make_pattern(ocap);
				$j("#AndroCaptcha_colors input").on("change",function(){
					check_for_hex();
				});
				$j("#form1").on("submit",function(){	//before submitting check for validity
					check_for_hex();
				});
			});
			function hex_rgb(hex)		//converts from hex value to an rgb color
			{
			    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
			    return result ? {
			        r: parseInt(result[1], 16),
			        g: parseInt(result[2], 16),
			        b: parseInt(result[3], 16)
			    } : null;
			}

			function check_for_hex()	//so that a proper rgb/rgba color is submitted
			{
				$j("#AndroCaptcha_colors input").each(function(){
					var v=$j(this).val();
					var ishex = /^#[0-9A-F]{6}$/i.test(v);
					if(ishex)
						{
							v=hex_rgb(v);
							rgb="rgb(" + v.r + "," + v.g + "," + v.b + ")"; 
						}
						else
							rgb=v;
					$j(this).val(rgb);
					update_variables();
					make_pattern(ocap);		//so that the user can preview before updating the color			
				});
			}

			function update_variables()		//so that a proper rgb/rgba color is submitted
			{
				bg_color=$j("#bg_color").val();
				arrow_color=$j("#arrow_color").val();
				inner_circle_color=$j("#inner_circle_color").val();
				init_circle_color=$j("#init_circle_color").val();
				outer_circle_color=$j("#outer_circle_color").val();
				line_color=$j("#line_color").val();
			}
			</script>
			<table id="AndroCaptcha_colors" >
			<tr><td>Arrow Color:</td><td><input type="text" id="arrow_color" name="AndroCaptcha_arrow_color" value='.$arrow_color.' /></td></tr>
			<tr><td>Line Color:</td><td><input type="text" id="line_color" name="AndroCaptcha_line_color" value='.$line_color.' /></td></tr>
			<tr><td>Inner Circle Color:</td><td><input type="text" id="inner_circle_color" name="AndroCaptcha_inner_circle_color" value='.$inner_circle_color.' /></td></tr>
			<tr><td>Outer Circle Color:</td><td><input type="text" id="outer_circle_color" name="AndroCaptcha_outer_circle_color" value='.$outer_circle_color.' /></td></tr>
			<tr><td>Initial Circle Color:</td><td><input type="text" id="init_circle_color" name="AndroCaptcha_init_circle_color" value='.$init_circle_color.' /></td></tr>
			<tr><td>Background Color:</td><td><input type="text" id="bg_color" name="AndroCaptcha_bg_color" value='.$bg_color.' /></td></tr>
			</table>
			</div>
			<input type="submit" value="Save" />
		</form>';
	}
?>