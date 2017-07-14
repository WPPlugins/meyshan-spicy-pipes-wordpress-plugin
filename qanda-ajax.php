<?php
header("Content-type: text/html; charset=UTF-8"); 

$tags = isset($_REQUEST['tags']) ? $_REQUEST['tags'] : '' ;
$numbertoshow = isset($_REQUEST['number']) ? $_REQUEST['number'] : 3;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;


$photos = get_photos($tags, $numbertoshow, $page);
$questions = get_questions($tags,$numbertoshow,$page);
if (count($questions['Questions']) < $numbertoshow)
		$numbertoshow = count($questions['Questions']);

echo '<ol id="qandalist">';
for ($i = 1;$i <= $numbertoshow;$i++)
{

$photourl = 'http://farm'.$photos['photos']['photo'][$i-1]['farm'].'.static.flickr.com/'.$photos['photos']['photo'][$i-1]['server'].'/'.$photos['photos']['photo'][$i-1]['id'].'_'.$photos['photos']['photo'][$i-1]['secret'].'_s.jpg';

$bigphotourl = 'http://farm'.$photos['photos']['photo'][$i-1]['farm'].'.static.flickr.com/'.$photos['photos']['photo'][$i-1]['server'].'/'.$photos['photos']['photo'][$i-1]['id'].'_'.$photos['photos']['photo'][$i-1]['secret'].'.jpg';


$photopageurl = 'http://www.flickr.com/photos/'.$photos['photos']['photo'][$i-1]['owner'].'/'.$photos['photos']['photo'][$i-1]['id'];

	echo '<li><h2>';
	if (isset($photos['photos']['photo'][$i-1]['id']))
		echo '<a href ="'.$bigphotourl.'" class="thickbox" title="'.$photopageurl.'"><img src="'.$photourl.'" /></a>';
	echo '<a href="http://answers.yahoo.com/question/index?qid='.$questions['Questions'][$i-1]['id'].'&KeepThis=true&TB_iframe=true&height=400&width=600" class="thickbox">'.wordtrim($questions['Questions'][$i-1]['Subject']).'</a></h2>';
	echo '<p>'.wordtrim($questions['Questions'][$i-1]['Content']).'</p>';
	$answer = get_answer($questions['Questions'][$i-1]['id']);
	//print_r($answer);
	echo '<h2>Answer:</h2>';
	echo '<p>'.$answer['Questions'][0]['ChosenAnswer'].'</p></li>';
}
echo '<ol>';

function get_questions($query,$number = 3,$page = 1)
{
	$params = array(
		'appid'	=> 'gN3qzZrV34Hr1N1PozBgwWp7f16ylARxjsRD7Wnf7BJj19OhHJxptGBojM3_7K4DfmjR',
		'type'	=> 'resolved',
		'output'	=> 'php',
		'query'	=> $query,
		'results' => $number,
		'start' => (($page - 1)*$number)
	);

	$encoded_params = array();

	foreach ($params as $k => $v){

		$encoded_params[] = urlencode($k).'='.urlencode($v);
	}

	$url = 'http://answers.yahooapis.com/AnswersService/V1/questionSearch?' . implode('&', $encoded_params);;

	$rsp = get_content($url);

	return(unserialize($rsp));
}

function get_answer($quesid)
{
	$params = array(
	'appid'	=> 'gN3qzZrV34Hr1N1PozBgwWp7f16ylARxjsRD7Wnf7BJj19OhHJxptGBojM3_7K4DfmjR',
	'output'	=> 'php',
	'question_id'	=> $quesid,
	);

	$encoded_params = array();

	foreach ($params as $k => $v){

		$encoded_params[] = urlencode($k).'='.urlencode($v);
	}

	$url = 'http://answers.yahooapis.com/AnswersService/V1/getQuestion?' . implode('&', $encoded_params);;

	$rsp = get_content($url);

	return( unserialize($rsp));
}

function get_photos($tags, $number, $page = 1)
{
	#
	# build the API URL to call
	#

	$params = array(
	'api_key'	=> 'bc4203acf92b461a56eb4484127f4c5c',
	'method'	=> 'flickr.photos.search',
	'tags' 		=>  $tags,
	'format'	=> 'php_serial',
	'per_page'	=>  $number,
	'page'		=> 	$page,
	'sort'		=>  'interestingness-desc'
	);

	$encoded_params = array();

	foreach ($params as $k => $v){

	$encoded_params[] = urlencode($k).'='.urlencode($v);
	}


	#
	# call the API and decode the response
	#

	$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);

	$rsp = get_content($url);

	$rsp_obj = unserialize($rsp);


	#
	# display the photo title (or an error if it failed)
	#

	if ($rsp_obj['stat'] == 'ok')
		return( $rsp_obj);
	/* }else{

		echo $rsp_obj['message'];
	} */
}

function get_content($url)
{
	if(ini_get('allow_url_fopen'))
	{
		return file_get_contents($url);
	}
	else if(function_exists('curl_init'))
	{
		$ch = curl_init();
		$timeout = 5; // set to zero for no timeout
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return $file_contents;
	}
	else
	{
		echo '<strong>This plugin requires you to have either allow_url_fopen or cURL.  Please enable allow_url_fopen or install cURL to continue.</strong>';
	}		
}

function wordtrim($text) {

   $words = explode(' ', $text);
	array_walk($words,'chopword');
   return(implode(' ', $words));
	
	
}
function chopword(&$word, $key) {
		$limit=25;
		if(strlen($word) > $limit)
       {
        		$word = substr($word,0,$limit);
       }
	}
?>