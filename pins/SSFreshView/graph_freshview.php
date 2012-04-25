<?php
include(dirname(__FILE__) . '/../../lib/load-wp.php');

if(!defined('SLIMSTATPATH')) {die("Please activate Wp-SlimStat-Ex");}

//require_once(SLIMSTATPATH . 'lib/pins.php');
require_once('pin.php');

$intervals = array('day', 'week', 'month', 'year');
$types = array('all', 'common', 'feed');
$interval = (isset($_GET['ssfv_interval'])) ? $_GET['ssfv_interval'] : 'day';
$type = (isset($_GET['ssfv_type'])) ? $_GET['ssfv_type'] : 'all';

$FV = new SSFreshView();

$cachelimit = (int)$FV->prefs['cachelimit'];
if( $cachelimit > 0 ) {
	if ( extension_loaded('zlib') and !ini_get('zlib.output_compression') and ini_get('output_handler') != 'ob_gzhandler' and ((version_compare(phpversion(), '5.0', '>=') and ob_get_length() == false) or ob_get_length() === false) ) {
		ob_start('ob_gzhandler');
	}
	header("Cache-Control: public");
	header("Pragma: cache");
	$offset = 60*$cachelimit;
	$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
	$LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";
	header($ExpStr);
	header($LmStr);
}

header("Content-type: image/svg+xml");

if(!in_array($interval, $intervals) || !in_array($type, $types)) {
	echo $FV->generateSVG_DataError();
	exit();
}

switch($interval) {
	/* Past day *************************************************/
	case 'day':
		echo $FV->generateSVG_PastDay($type);
	break;
	/* Past week ************************************************/
	case 'week':
		echo $FV->generateSVG_PastWeek($type);
	break;
	/* Past month ***********************************************/
	case 'month':
		echo $FV->generateSVG_PastMonth($type);
	break;
	/* Past year ************************************************/
	case 'year':
		echo $FV->generateSVG_PastYear($type);
	break;
}
?>