<?php
if (isset($_GET['php_track']) && '1' == $_GET['php_track'] ) :
// check query vars
if ( !isset( $_GET["ref"] ) || !isset( $_GET["res"] ) || !file_exists(dirname(__FILE__) . '/external.php') ) {
	header( "Content-Type: image/gif" );
	readfile( dirname(__FILE__)  . "/../css/blank.gif" );
	die();
}
$site_id = ( isset($_GET['site_id']) && '' != $_GET['site_id'] && is_int($_GET['site_id']) ) ? (int)$_GET['site_id'] : 1;
define('SLIMSTAT_EXTRACK_JS', true);
define('SLIMSTAT_SITE_ID', $site_id);// dobule check site id is integral
require_once (dirname(__FILE__) . '/external.php');

$img_file = SLIMSTATPATH . "css/blank.gif";
header('Content-Type: application/octet-stream');
header('Content-Length: '.filesize($img_file));
readfile($img_file);
exit();
else :
if ( extension_loaded('zlib') and !ini_get('zlib.output_compression') and ini_get('output_handler') != 'ob_gzhandler' and ((version_compare(phpversion(), '5.0', '>=') and ob_get_length() == false) or ob_get_length() === false) ) {
	ob_start('ob_gzhandler');
}

// The headers below tell the browser to cache the file and also tell the browser it is JavaScript.
header("Cache-Control: public");
header("Pragma: cache");

$offset = 60*60*24*60;
$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
$LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";
header($ExpStr);
header($LmStr);
header('Content-Type: text/javascript; charset: UTF-8');
$self_url  = ( isset($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
$self_url .= $_SERVER['HTTP_HOST'];
$self_url .= $_SERVER['PHP_SELF'];
?>
function SlimStatExTrack() {
	var slimstatexfile = '<?php echo $self_url; ?>';
	var ref = encodeURIComponent(document.referrer);
	var res = encodeURIComponent(document.URL);

	var img = document.createElement('img');
	img.width = 1;
	img.height = 1;
	img.alt = '';
	img.src = slimstatexfile+'?php_track=1&ref='+ref+'&res='+res+'&site_id='+SLIMSTAT_SITE_ID;

	document.body.appendChild(img);
}
<?php
exit();
endif;
?>