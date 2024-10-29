<script type="text/javascript" >
document.onselectstart = function(){ return false; }
$j = jQuery.noConflict();
var c=$j("#AndroCaptcha_can");
can=c[0];
var ctx=can.getContext("2d");
ctx.lineWidth = 15;
var w=c.width();
var h=c.height();
var radius=w/15;			//radius of each circle
var pos_x=[1/8,1/2,7/8];	//position ratios of the circles wrt width and height
var pos_y=[1/8,1/2,7/8];

<?php
	get_options();
if(isset($_SESSION['options']))
	{
	foreach($_SESSION['options'] as $key=>$value)
	{
		$$key=$value;		//because keys of the array are the names of the variables
		echo "var ".$key." = \"".$value."\";";		//assigning in js as well
	}
}
else 			//roll back to default colors
{
	echo 
        "var bg_color = \"rgb(230,184,184)\";".
        "var arrow_color = \"rgba(32,232,46,0.5)\";".
        "var inner_circle_color = \"rgb(142,53,239)\";".
        "var init_circle_color = \"rgb(154,254,255)\";".
        "var outer_circle_color = \"rgb(255,243,128)\";".
        "var line_color = \"rgba(237,225,104,0.5)\";";	
}

if(!function_exists(get_options))
{
	function get_options()			//this definition doesn't contain default colors as we have taken care of that in js
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
}
}
?>
var drawing=false;
var l=c.offset().left;
var t=c.offset().top;
if(!cap)
var cap=new Array();
jQuery(document).ready(function($){

	init();

	$j("#AndroCaptcha_can").on("mousedown",function(e){
			x=e.pageX - l;
			y=e.pageY - t;
			for(i=0;i<3;i++)
			{
				for(j=0;j<3;j++)
				{
					if(get_distance(x,w*pos_x[i],y,h*pos_y[j])<=radius)
						{
							init();
							drawing=true;
							//current_circle(i,j);
							cap.splice(0,cap.length);
							$("#AndroCaptcha_value").val("");
						}
				}
			}
			
	});

	$j("#AndroCaptcha_can").mousemove(function(e){
		if(drawing)
		{
			$(this).css("cursor","crosshair");
			x=e.pageX - l;
			y=e.pageY - t;
			for(i=0;i<3;i++)
			{
				for(j=0;j<3;j++)
				{
					if(get_distance(x,w*pos_x[i],y,h*pos_y[j])<=radius)
					{
						index=cap.indexOf(3*j+i+1);
						if(index==-1)
						{
							ctx.lineWidth = 15;
							l_index=cap[cap.length-1];
							l_j=Math.floor((l_index-1)/3);
							l_i=l_index-1-3*l_j;
							current_circle(i,j);
							make_line(l_i,l_j,i,j);
						}
					}						
				}
			}
			$("#AndroCaptcha_value").val(cap.toString());
		}
	});	
	
	$(document).mouseup(function(){
		drawing=false;
	});
});

function init()					//basic initiallisation
{
	ctx.fillStyle=bg_color;
	ctx.fillRect(0,0,w,h);
	for(i=0;i<3;i++)
	{
		for(j=0;j<3;j++)
		{
		 	ctx.moveTo(w*pos_x[i],h*pos_y[j]);
		 	ctx.arc(w*pos_x[i],h*pos_y[j],radius,0,2*Math.PI);				
		}
	}
	ctx.fillStyle =init_circle_color;
	ctx.fill();
}

function current_circle(i,j)	//styling the circle currently in focus
{
	ctx.lineWidth = 15;
	ctx.beginPath();
	ctx.moveTo(w*pos_x[i],h*pos_y[j]);
	ctx.arc(w*pos_x[i],h*pos_y[j],radius,0,Math.PI*2);
	ctx.fillStyle=inner_circle_color;
	ctx.fill();
	ctx.beginPath();
	ctx.arc(w*pos_x[i],h*pos_y[j],radius,0,Math.PI*2);
	ctx.strokeStyle=outer_circle_color;
	ctx.stroke();
}

function get_distance(x1,x2,y1,y2)	//distance formula b/w 2 points
{
	return(Math.sqrt(Math.pow(x1-x2,2) + Math.pow(y1-y2,2)));
}

function make_arrow(slope,l_i,l_j)	//function to paint the arrows
{
	var arr_w=12;					//half arrow width
	slope2=-1/slope;
	angle2=Math.atan(slope2);
	arrl_x=arr_w*Math.cos(angle2);
	arrl_y=arr_w*Math.sin(angle2);
	ctx.beginPath();
	ctx.moveTo(w*pos_x[l_i], h*pos_y[l_j]);
	ctx.lineTo(w*pos_x[l_i] + arrl_x, h*pos_y[l_j] + arrl_y);
	ctx.lineTo(w*pos_x[l_i] + arr_x, h*pos_y[l_j] + arr_y)
	ctx.lineTo(w*pos_x[l_i] - arrl_x, h*pos_y[l_j] - arrl_y);
	ctx.lineTo(w*pos_x[l_i], h*pos_y[l_j]);
	ctx.fillStyle=arrow_color;
	ctx.lineWidth=2;
	ctx.fill();
}

function make_line(l_i,l_j,i,j,skip)		//quite obvious
{
	if(typeof(skip)==='undefined' && Math.abs(l_i-i)%2==0 && Math.abs(l_j-j)%2==0)	//so that skipping a circle wont be possible
	{
		t_i=(l_i+i)/2;
		t_j=(l_j+j)/2;
		temp_index=3*t_j+t_i+1;
		if(cap.indexOf(temp_index)==-1)
		{
			make_line(l_i,l_j,t_i,t_j);
			make_line(t_i,t_j,i,j);	
		}
		else
			make_line(l_i,l_j,i,j,true);
		
	}
	else
	{
		var arr_h=27;		//arrow height
		slope=h*(pos_y[j]-pos_y[l_j])/(w*(pos_x[i]-pos_x[l_i]));
		angle=Math.atan(slope);
		arr_x=arr_y=0;
		if(i>=l_i)
		{
		arr_x=arr_h*Math.cos(angle);
		arr_y=arr_h*Math.sin(angle);									
		}
		else if( i<l_i)
		{
		arr_x=-arr_h*Math.cos(angle);
		arr_y=-arr_h*Math.sin(angle);										
		}
		current_circle(l_i,l_j);
		make_arrow(slope,l_i,l_j);
		ctx.beginPath();
		ctx.moveTo(w*pos_x[l_i] + arr_x, h*pos_y[l_j] + arr_y);
		ctx.lineTo(w*pos_x[i] - arr_x ,h*pos_y[j]- arr_y);
		ctx.lineWidth=15;
		ctx.strokeStyle=line_color;
		ctx.stroke();
		cap.push(3*j+i+1);
	}
}

function make_pattern(orig_cap)
{
	var cap2=new Array();
	cap2=cap2.concat(orig_cap);
	var l_index=-1;
	init();
	while(cap2.length>0)
	{
		index=cap2[0];
		cap2.shift();
		j=Math.floor((index-1)/3);
		i=index-1-3*j;
		if(l_index!=-1)
		{
			l_j=Math.floor((l_index-1)/3);
			l_i=l_index-1-3*l_j;
			make_line(l_i,l_j,i,j);
		}
		l_index=index;		
	}
	current_circle(i,j);
}

</script>