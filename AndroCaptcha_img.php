<?php
session_start();				//Code to ggenerate the captcha in the form of image
$w=300;
$h=300;
$img=imagecreatetruecolor(300, 300);
$pos_x=array(1/8,1/2,7/8);
$pos_y=array(1/8,1/2,7/8);
$r=floor($w/7.5);							//Note radius is half for php's imagefilledarc function!!!
$c_w=15;									//Outer arc width
	$bg_color=imagecolorallocatealpha($img,230,184,184,0);				/*Default Colors*/
	$arrow_color=imagecolorallocatealpha($img,32,232,46,80);
	$inner_circle_color=imagecolorallocatealpha($img,142,53,239,0);
	$init_circle_color=imagecolorallocatealpha($img,154,254,255,0);
	$outer_circle_color=imagecolorallocatealpha($img,255,243,128,0);
	$line_color=imagecolorallocatealpha($img,237,225,104,80);			/*Default Colors*/
	if(isset($_SESSION['options']))
	{
		foreach($_SESSION['options'] as $key=>$value)
		{
			$replace=array("rgba(","rgb(",")");
			$temp_array=explode(",",str_replace($replace, "", $value));	//getting the r,g,b values as an array
			if(count($temp_array)===3)			//If the color is rgb() make it rgba(r,g,b,1);
				array_push($temp_array, 1);
			$$key=imagecolorallocatealpha($img, $temp_array[0], $temp_array[1], $temp_array[2] ,127-127*$temp_array[3] );	
		}
	}

$num=rand(3,6);						//making a random array for captcha of random length
$arr=array();
$l_index=-1;
for($i=0;$i<$num;$i++)
{
		do{
			$temp=rand(1,9);
		}while(!check_allowed_index($l_index,$temp,$arr));
		array_push($arr, $temp);
		$l_index=$temp;
}									//making a random array for captcha
$_SESSION["AndroCaptcha_value"]=$arr;
//if(!isset($_GET['pat']))			//use thses options for debugging
make_pattern($arr);
//else 								//use thses options for debugging
//make_pattern(array(3,1,4,5,9));	//use thses options for debugging
//if(!isset($_GET['debug']))		//use thses options for debugging
header("Content-type:Image/png");
imagefilter($img, IMG_FILTER_SMOOTH,4);
imagepng($img);
imagedestroy($img);

function check_allowed_index($l_index,$index,$arr)		//also checks for skipping a circle in the pattern
{
	if($l_index===-1)
		return true;
	else
	{
		if(in_array($index, $arr))
			return false;
		$t_i=($index-1)%3;			//getting the i,j from index
		$t_j=($index-1-$t_i)/3;
		$t_l_i=($l_index-1)%3;
		$t_l_j=($l_index-1-$t_l_i)/3;
		if(abs($t_i-$t_l_i)%2==0 && abs($t_j-$t_l_j)%2==0)
			return false;
		else
			return true;
	}
}


function init()
{
	global $img,$bg_color,$w,$h,$pos_x,$pos_y,$r,$init_circle_color;
	imagefill($img, 0, 0, $bg_color);
	for($i=0;$i<3;$i++)
		for($j=0;$j<3;$j++)
			imagefilledellipse($img, $w*$pos_x[$i], $h*$pos_y[$j], $r, $r,$init_circle_color);
}

function current_circle($i,$j)
{
	global $img,$w,$h,$c_w,$pos_x,$pos_y,$r,$inner_circle_color,$outer_circle_color;
	imagefilledarc($img, $w*$pos_x[$i], $h*$pos_y[$j], $r, $r, 0, 360, $inner_circle_color,IMG_ARC_PIE);
	imagefilledarc($img, $w*$pos_x[$i], $h*$pos_y[$j], $r+15, $r+15, 0, 360, $outer_circle_color,IMG_ARC_NOFILL);
	imagefilledarc($img, $w*$pos_x[$i], $h*$pos_y[$j], $r-$c_w, $r -$c_w , 0, 360, $outer_circle_color,IMG_ARC_NOFILL);
	imagefilltoborder($img, $w*$pos_x[$i] + $r/2 -3, $h*$pos_y[$j] , $outer_circle_color, $outer_circle_color);
}

function make_line($l_i,$l_j,$i,$j)
{
	global $img,$w,$h,$pos_x,$pos_y,$r,$line_color;
	$height=28;
	if($i===$l_i)
		{
			$arr_y=$height;
			if($j<$l_j)
				$arr_y*=-1;
			$arr_x=0;
		}

	else
	{
		$slope=($h*$pos_y[$j]-$h*$pos_y[$l_j])/($w*$pos_x[$i]-$w*$pos_x[$l_i]);
		$angle=atan2($h*$pos_y[$j]-$h*$pos_y[$l_j], $w*$pos_x[$i]-$w*$pos_x[$l_i]);
		$arr_x=$height*cos($angle);
		$arr_y=$height*sin($angle);

	}
	thick($w*$pos_x[$l_i] + $arr_x, $h*$pos_y[$l_j] + $arr_y, $w*$pos_x[$i] - $arr_x, $h*$pos_y[$j] - $arr_y, $line_color,15);
	make_arrow($w*$pos_x[$l_i],$h*$pos_y[$l_j],($w*$pos_x[$l_i]) +$arr_x,($h*$pos_y[$l_j])+$arr_y);
}

function make_pattern($arr)
{

	init();
	$l_index=-1;
	for($k=0;$k<count($arr);$k++)
				{
					$index=$arr[$k];
		$i=($index-1)%3;
		$j=($index-1-$i)/3;	
		current_circle($i,$j);
	}
	for($k=0;$k<count($arr);$k++)
	{
		$index=$arr[$k];
		$i=($index-1)%3;
		$j=($index-1-$i)/3;		
		if($l_index!=-1)
		{
			$l_i=($l_index-1)%3;
			$l_j=($l_index-1-$l_i)/3;
			make_line($l_i,$l_j,$i,$j);
		}
		$l_index=$index;

	}
}

function make_arrow($x1,$y1,$x2,$y2)
{
	global $img,$arrow_color;
	$width=26;
	if($x2===$x1)
	{
		$points=array(
			$x1+$width/2,$y1,
			$x1-$width/2,$y1,
			$x2,$y2);
	}

	else
	{
		$slope=($y2-$y1)/($x2-$x1);
		$angle=atan($slope);
		$arr_x=($width/2)*sin($angle);
		$arr_y=($width/2)*cos($angle);
		$points=array(
			$x1+$arr_x,$y1-$arr_y,
			$x1-$arr_x,$y1+$arr_y,
			$x2,$y2);
	}
	imagefilledpolygon($img, $points, 3, $arrow_color);
}

function thick($x1,$y1,$x2,$y2)	//func for thick line
{
	global $img,$line_color;
	$w=7;
	if($x1===$x2)
	{
		$points=array(
			$x1-$w,$y1,
			$x1+$w,$y1,
			$x2+$w,$y2,
			$x2-$w,$y2);
	}
	 else
	 {
		$slope=($y2-$y1)/($x2-$x1);
		$angle=atan($slope);
		$line_y=$w*cos($angle);
		$line_x=$w*sin($angle);
		$points=array(
			$x1-$line_x,$y1+$line_y,
			$x1+$line_x,$y1-$line_y,
			$x2+$line_x,$y2-$line_y,
			$x2-$line_x,$y2+$line_y);
	}
	imagefilledpolygon($img, $points, 4, $line_color);
}

?>