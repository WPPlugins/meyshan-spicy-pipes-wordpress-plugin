<?php
/*
Plugin Name: Meyshans SpicyPipes
Version: 2.2
Plugin URI: http://www.spicyexpress.net/
Description: This wordpress plugin, once installed and configured displays question and answer from yahoo answers and related photos from Flickr.  Developed for<a href="http://www.meyshan.coml"> Meyshan Spicypipes1</a> at Spicyexpress.net. Based on <a href="http://www.Spicypipes.com">SpicyPipes.com - A api magic</a> by Dayaparan.
Author: P. Dayaparan 
Version: 1.0
Author URI: http://www.spicyexpress.net
*/
$pluginpath = str_replace(str_replace('\\', '/', ABSPATH), get_settings('siteurl').'/', str_replace('\\', '/', dirname(__FILE__))).'/';

function qanda_header() {
global $pluginpath;
echo '<script type="text/javascript" src="'. get_bloginfo('wpurl') . '/wp-includes/js/tw-sack.js"></script>
<script type="text/javascript" src="'. $pluginpath .'thickbox/jquery-latest.pack.js"></script>
<script type="text/javascript" src="'. $pluginpath .'thickbox/thickbox.js"></script>
<script type="text/javascript">
		var loadingImagePath = "'. $pluginpath . 'thickbox/loadingAnimation.gif";
</script>	
<link rel="stylesheet" href="'.$pluginpath .'thickbox/thickbox.css" type="text/css" media="screen" />';
echo '<style type="text/css">
#qandalist img {
float:left;
margin:0 3px 3px 0;
border:solid 1px;
padding:1px;
}
</style>';
}

function qanda($echo = true,$overridetags = NULL) {
	$options = get_option('widget_qanda');
	$tags = (($overridetags != '') ? $overridetags : $options['defaulttags']);
	if($echo) {
		echo qanda_return ($tags,$options['number']);
	}
	else {
		return qanda_return ($tags,$options['number']);
	}
}

function qanda_return ($defaulttags,$number) {
global $pluginpath;
$id = rand(0, 100000);
$output ='<script type="text/javascript">
	var page;
	function ask_me_'.$id.'() {
		page = 1;
		get_qanda_'.$id.'();
	}
	function loadTest_'.$id.'() {
		setTimeout("TB_init()",100);
	}
	function get_qanda_'.$id.'() {
		document.getElementById(\'qandaresult-'.$id.'\').innerHTML="<strong>Loading...</strong>"
		var url = \''.$pluginpath.'qanda-ajax.php?number='.$number.'&page=\'+page+\'&tags=\' + document.getElementById(\'qandatags-'.$id.'\').value;
		ajax = new sack(url);
		ajax.element = \'qandaresult-'.$id.'\';
		ajax.onLoaded = loadTest_'.$id.';
		ajax.runAJAX();
		document.getElementById(\'qandahide-'.$id.'\').style.display = \'inline\';
		document.getElementById(\'qandamore-'.$id.'\').style.display = \'inline\';		
	}
	function hide_qanda_'.$id.'() {
		document.getElementById(\'qandaresult-'.$id.'\').innerHTML=\'\';
		document.getElementById(\'qandahide-'.$id.'\').style.display = \'none\';
		document.getElementById(\'qandamore-'.$id.'\').style.display = \'none\';		
	}
	function more_qanda_'.$id.'() {
		page = page + 1;
		get_qanda_'.$id.'();		
	}
</script>';
$output .='<p><form style="display:inline;" action="javascript:ask_me_'.$id.'();"><input id="qandatags-'.$id.'" value="'. $defaulttags .'"/>';
$output .= '<button type="submit">Ask Me</button></form><button id="qandamore-'.$id.'" onclick="more_qanda_'.$id.'();">More Answers</button><button id="qandahide-'.$id.'" onclick="hide_qanda_'.$id.'();">Hide Answers</button><br />(Type any question and press the "Ask Me" button)</p>
<div id="qandaresult-'.$id.'"></div>
<script type="text/javascript">
	page = 1;
	get_qanda_'.$id.'();
</script>
';
return($output);
}

function content_qanda($content) {
	if(preg_match('/<!--spicypipes.com[\(]*(.*?)[\)]*-->/',$content,$matches)) {
		$customtags =$matches[1];
		$content = preg_replace('/<!--spicypipes.com(.*?)-->/',qanda(false,$customtags), $content);
	}
	return $content;
}

function qanda_addMenu() {
	add_options_page("Spicypipes Q & A", "Spicypipes Q & A" , 8, __FILE__, 'qanda_optionsMenu');
}	

function qanda_optionsMenu() {
	echo '<div style="width:250px; margin:auto;"><form method="post">';
	qanda_control();
	echo '<p class="submit"><input value="Save Changes »" type="submit"></form></p></div>';
}

function qanda_control() {
if(!(ini_get('allow_url_fopen') || function_exists('curl_init')))
	{
		echo '<strong>This plugin requires you to have either allow_url_fopen or cURL.  Please enable allow_url_fopen or install cURL to continue.</strong>';
	}
	else
	{
	$options = get_option('widget_qanda');
	if ( !is_array($options) )
		$options = array('title'=>'Spicypipes Q & A', 'number'=>3);			
	if ( $_POST['qanda-submit'] ) {
		$options['title'] = strip_tags(stripslashes($_POST['qanda-title']));
		$options['defaulttags'] = strip_tags(stripslashes($_POST['qanda-defaulttags']));			
		$options['number'] = strip_tags(stripslashes($_POST['qanda-number']));						
		update_option('widget_qanda', $options);
	}

	$title = htmlspecialchars($options['title'], ENT_QUOTES);

	echo '<p style="text-align:right;"><label for="qanda-title">Title:</label><br /> <input style="width: 200px;" id="qanda-title" name="qanda-title" type="text" value="'.$title.'" /></p>';
	echo '<p style="text-align:right;"><label for="qanda-defaulttags">Default Tags:</label><br /> <input style="width: 200px;" id="qanda-defaulttags" name="qanda-defaulttags" type="text" value="'.$options['defaulttags'].'" /></p>';
	echo '<p style="text-align:right;"><label for="qanda-number">Number to Show:</label><br /> <input style="width: 200px;" id="qanda-number" name="qanda-number" type="text" value="'.$options['number'].'" /></p>';																
	echo '<input type="hidden" id="qanda-submit" name="qanda-submit" value="1" />';
	}
}


function widget_qanda_init() {



	if (!function_exists('register_sidebar_widget'))
		return;
	
	
	function widget_qanda($args) {
		extract($args);
				
		$options = get_option('widget_qanda');
		$title = $options['title'];

		echo $before_widget;
		echo $before_title . $title . $after_title;
		qanda();
		echo $after_widget;
	}	
	
			
	register_sidebar_widget('Spicypipes Q & A', 'widget_qanda');
	register_widget_control('Spicypipes Q & A', 'qanda_control', 250, 190);
}
add_action('admin_menu', 'qanda_addMenu');
add_action('wp_head', 'qanda_header');
add_filter('the_content', 'content_qanda');
add_action('plugins_loaded', 'widget_qanda_init');

?>