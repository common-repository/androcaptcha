<?php  
    /* 
    Plugin Name: AndroCaptcha - Android like Captcha for Wordpress
    Plugin URI: http://leetmatrix.blogspot.com/2013/06/androcaptcha-for-wordpress.html
    Description: Plugin for displaying a captcha just like the Android Lock Screen
    Author: SuperNOVA
    Version: 1.0 
    Author URI: http://facebook.com/saransh.dhingra
    */  
define('path_dir',plugin_dir_url(__FILE__));

    add_action('admin_menu', 'AndroCaptcha_actions');
    add_action('login_form','show_captcha');
    add_filter( 'authenticate', 'check_captcha', 10, 3 );
    add_action('init', 'load_jquery');

    function load_jquery()
    {
        wp_enqueue_script("jquery");
    }

    function add_settings_link( $links )
    {
        $settings_link = '<a href="options-general.php?page=andro_cap">Settings</a>';
        array_push( $links, $settings_link );
        return $links;
    }


    function AndroCaptcha_actions()
    {
        $plugin = plugin_basename( __FILE__ );
        add_filter( "plugin_action_links_$plugin", 'add_settings_link' );
        add_options_page("AndroCaptcha", "AndroCaptcha Settings", 1, "andro_cap", "AndroCaptcha_admin");
    }

    function AndroCaptcha_admin() {  
        get_options();
    	include('AndroCaptcha_admin.php');  
	}

	function show_captcha()
	{
            get_options();
			echo '
            <input type="hidden" value="" id="AndroCaptcha_value" name="AndroCaptcha_value" />
            <table id="AndroCap_table">
            <tr>
            <td>
            <canvas width="300" height="300" id="AndroCaptcha_can"  style="margin:10px;"  >
			</canvas>
            </td>
            <td>
           <img src="'.path_dir.'AndroCaptcha_img.php" id="AndroCaptcha_img" />
           </td>
           </tr>
           </table>
           <script>
           $j = jQuery.noConflict();
           var o1=$j("#AndroCap_table").offset();
           $j("#AndroCap_table").offset({left:o1.left - 150 ,top:o1.top});
           </script>
			';
			include_once('AndroCaptcha.php');
	}			//end of show_captcha() 

    function check_captcha($user,$username,$password)
    {
        session_start();
        if(isset($_POST['AndroCaptcha_value']))
        {
            $cap_value=explode(",",addslashes($_POST['AndroCaptcha_value']));
            $arr=$_SESSION["AndroCaptcha_value"];
            if(!compare_arrays($arr,$cap_value))
            {
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: The captcha you entered was incorrect!") );   
            }
        }
        $_SESSION["AndroCaptcha_value"]=get_random_array();
        return $user;
    }

function get_random_array()
{
    $num=rand(3,6);
    $arr=array();
    $l_index=-1;
    for($i=0;$i<$num;$i++)
    {
            do{
                $temp=rand(1,9);
            }while(!check_allowed_index($l_index,$temp,$arr));
            array_push($arr, $temp);
            $l_index=$temp;
    }
    return $arr;    
}

if(!function_exists(check_allowed_index))
{
function check_allowed_index($l_index,$index,$arr)
{
    if($l_index===-1)
        return true;
    else
    {
        if(in_array($index, $arr))
            return false;
        $t_i=($index-1)%3;
        $t_j=($index-1-$t_i)/3;
        $t_l_i=($l_index-1)%3;
        $t_l_j=($l_index-1-$t_l_i)/3;
        if(abs($t_i-$t_l_i)%2==0 && abs($t_j-$t_l_j)%2==0)
            return false;
        else
            return true;
    }
}
}
if(!function_exists(compare_arrays))
{
       function compare_arrays($arr,$cap)
    {
        if(count($arr)!=count($cap))
            return false;
        for($i=0;$i<count($arr);$i++)
        {
            if($arr[$i]!=$cap[$i])
                return false;
        }
        return true;
    }
}

function get_options()
{
                $options=get_option('AndroCaptcha_options');
                if($options!==false)    //if options were read successfully!
                {
                    $_SESSION['options']=array(
                    "bg_color"=>$options['AndroCaptcha_bg_color'],
                    "arrow_color"=>$options['AndroCaptcha_arrow_color'],
                    "inner_circle_color"=>$options['AndroCaptcha_inner_circle_color'],
                    "init_circle_color"=>$options['AndroCaptcha_init_circle_color'],
                    "outer_circle_color"=>$options['AndroCaptcha_outer_circle_color'],
                    "line_color"=>$options['AndroCaptcha_line_color']);
                }
                else    //roll back to default options
                {
                    $_SESSION['options']=array(
                    "bg_color"=>"rgb(230,184,184)",
                    "arrow_color"=>"rgba(32,232,46,0.5)",
                    "inner_circle_color"=>"rgb(142,53,239)",
                    "init_circle_color"=>"rgb(154,254,255)",
                    "outer_circle_color"=>"rgb(255,243,128)",
                    "line_color"=>"rgba(237,225,104,0.5)");
                }
}

?>