<?php
if ( !defined('SLIMSTATPATH') || !defined('SLIMSTAT_EXTRACK') )
	die('Do not use this file directly');

// if user didn't defined site id, call default external site id.
if ( !defined('SLIMSTAT_SITE_ID') ) define('SLIMSTAT_SITE_ID', 1);

/* External PHP program tracking
---------------------------------------------------------------*/
function sstrack_external() {
	global $slimtrack_ext;

/* BEGIN wp-settings.php 
---------------------------------------------------------------*/
/*if ( !defined('WP_MEMORY_LIMIT') )
	define('WP_MEMORY_LIMIT', '32M');

if ( function_exists('memory_get_usage') && ( (int) @ini_get('memory_limit') < abs(intval(WP_MEMORY_LIMIT)) ) )
	@ini_set('memory_limit', WP_MEMORY_LIMIT);*/


if (!function_exists('wp_unregister_globals')):
function wp_unregister_GLOBALS() {
	if ( !ini_get('register_globals') )
		return;

	if ( isset($_REQUEST['GLOBALS']) )
		die('GLOBALS overwrite attempt detected');

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v )
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = NULL;
			unset($GLOBALS[$k]);
		}
}
endif;
wp_unregister_GLOBALS();

//unset( $wp_filter, $cache_lastcommentmodified, $cache_lastpostdate );

/**
 * The $blog_id global, which you can change in the config allows you to create a simple
 * multiple blog installation using just one WordPress and changing $blog_id around.
 *
 * @global int $blog_id
 * @since 2.0.0
 */
/*if ( ! isset($blog_id) )
	$blog_id = 1;*/

// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {

	// IIS Mod-Rewrite
	if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	}
	// IIS Isapi_Rewrite
	else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	}
	else
	{
		// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
		if ( isset($_SERVER['PATH_INFO']) ) {
			if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
				$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
			else
				$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
		}

		// Append the query string if it exists and isn't null
		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}

// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

// Fix for Dreamhost and other PHP as CGI hosts
if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false)
	unset($_SERVER['PATH_INFO']);

// Fix empty PHP_SELF
$PHP_SELF = $_SERVER['PHP_SELF'];
if ( empty($PHP_SELF) )
	$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);

if ( version_compare( '4.3', phpversion(), '>' ) ) {
	return;
//	die( sprintf( /*WP_I18N_OLD_PHP*/'Your server is running PHP version %s but WordPress requires at least 4.3.'/*/WP_I18N_OLD_PHP*/, php_version() ) );
}

// Add define('WP_DEBUG',true); to wp-config.php to enable display of notices during development.
/*if (defined('WP_DEBUG') and WP_DEBUG == true) {
	error_reporting(E_ALL);
} else {
	error_reporting(E_ALL ^ E_NOTICE ^ E_USER_NOTICE);
}*/

if ( !defined('SLIMSTAT_WPROOT') )
	define( 'SLIMSTAT_WPROOT', preg_replace('|wp-content/plugins.*$|','', str_replace('\\', '/', __FILE__)) );
// Ignore wordpress mu
require_once (SLIMSTAT_WPROOT . 'wp-includes/version.php');
if ( strpos($wp_version, 'wordpress-mu') !== false || isset($wporg_version) || isset($wpmu_version) )
	return;// external tracking does not support wpmu by now.

require_once (SLIMSTAT_WPROOT . 'wp-includes/compat.php');

/* START functions.php 
------------------------------------*/
if (!function_exists('get_option')):
function get_option( $setting ) {// modified - no cache, sanitize, filters
	global $wpdb;
	$row = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name = '$setting' LIMIT 1" );
	if ( is_object( $row) ) { // Has to be get_row instead of get_var because of funkiness with 0, false, null values
		$value = $row->option_value;
	} else { // option does not exist, so we must cache its non-existence
		return false;
	}

	// If home is not set use siteurl.
	if ( 'home' == $setting && '' == $value )
		return get_option( 'siteurl' );

	if ( in_array( $setting, array('siteurl', 'home', 'category_base', 'tag_base') ) )
		$value = untrailingslashit( $value );

	return maybe_unserialize( $value );
}
endif;
if (!function_exists('update_option')):
function update_option( $option_name, $newvalue ) {// modified - no cache, sanitize, filters
	global $wpdb;

	$safe_option_name = $wpdb->escape( $option_name );
	$oldvalue = get_option( $safe_option_name );

	// If the new and old values are the same, no need to update.
	if ( $newvalue === $oldvalue )
		return false;

	if ( false === $oldvalue ) {
		$insert = true;
	}

	$newvalue = maybe_serialize( $newvalue );

	if ($insert === true)
		$wpdb->query(	$wpdb->prepare( "INSERT INTO $wpdb->options (option_name, option_value, autoload) VALUES (%s, %s, %s)", $option_name, $newvalue, 'yes' ) );
	else 
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->options SET option_value = %s WHERE option_name = %s", $newvalue, $option_name ) );
	if ( $wpdb->rows_affected == 1 ) {
		return true;
	}
	return false;
}
endif;
if (!function_exists('is_serialized')):
function is_serialized( $data ) {
	// if it isn't a string, it isn't serialized
	if ( !is_string( $data ) )
		return false;
	$data = trim( $data );
	if ( 'N;' == $data )
		return true;
	if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
		return false;
	switch ( $badions[1] ) {
		case 'a' :
		case 'O' :
		case 's' :
			if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
				return true;
			break;
	}
	return false;
}
endif;
if (!function_exists('maybe_unserialize')):
function maybe_unserialize( $original ) {
	if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		if ( false !== $gm = @unserialize( $original ) )
			return $gm;
	return $original;
}
endif;
if (!function_exists('maybe_serialize')):
function maybe_serialize( $data ) {
	if ( is_string( $data ) )
		return $data;
	elseif ( is_array( $data ) || is_object( $data ) )
		return serialize( $data );
	if ( is_serialized( $data ) )
		return serialize( $data );
	return $data;
}
endif;
if (!function_exists('add_magic_quotes')):
function add_magic_quotes( $array ) {
	global $wpdb;

	foreach ( $array as $k => $v ) {
		if ( is_array( $v ) ) {
			$array[$k] = add_magic_quotes( $v );
		} else {
			$array[$k] = $wpdb->escape( $v );
		}
	}
	return $array;
}
endif;
if (!function_exists('wp_parse_args')):
function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}
endif;
/* END functions.php
------------------------------------*/

/* START wp-db.php 
------------------------------------*/
if (!class_exists('wpdb')):
// prevent WP default wpdb instance
$wpdb = '';
require_once(SLIMSTAT_WPROOT . 'wp-includes/wp-db.php');

$wpdb = new wpdb($slimtrack_ext['DB_USER'], $slimtrack_ext['DB_PASSWORD'], $slimtrack_ext['DB_NAME'], $slimtrack_ext['DB_HOST']);
endif;

$wpdb->prefix = $slimtrack_ext['table_prefix'];

$wpdb->options = $slimtrack_ext['table_prefix'] . 'options';
$GLOBALS['wpdb'] =& $wpdb;
/* END wp-db.php
------------------------------------*/
/*if (!function_exists('wp_cache_init')):
require_once (SLIMSTAT_WPROOT . 'wp-includes/cache.php');
endif;
wp_cache_init();*/


/* START formatting.php 
------------------------------------*/
if (!function_exists('backslashit')):
function backslashit($string) {
	$string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
	$string = preg_replace('/([a-z])/i', '\\\\\1', $string);
	return $string;
}
endif;
if (!function_exists('trailingslashit')):
function trailingslashit($string) {
	return untrailingslashit($string) . '/';
}
endif;
if (!function_exists('untrailingslashit')):
function untrailingslashit($string) {
	return rtrim($string, '/');
}
endif;
if (!function_exists('addslashes_gpc')):
function addslashes_gpc($gpc) {
	global $wpdb;

	if (get_magic_quotes_gpc()) {
		$gpc = stripslashes($gpc);
	}

	return $wpdb->escape($gpc);
}
endif;
if (!function_exists('stripslashes_deep')):
function stripslashes_deep($value) {
	 $value = is_array($value) ?
		 array_map('stripslashes_deep', $value):
		 stripslashes($value);

	 return $value;
}
endif;
if (!function_exists('seems_utf8')):
function seems_utf8($Str) { # by bmorel at ssi dot fr
	$length = strlen($Str);
	for ($i=0; $i < $length; $i++) {
		if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
		elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($Str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}
endif;
if (!function_exists('wp_parse_str')):
function wp_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array );
	$array = apply_filters( 'wp_parse_str', $array );
}
endif;
/* END formatting.php 
------------------------------------*/

/* START deprecated.php 
------------------------------------*/
if (!function_exists('get_settings')):
function get_settings($option) {
	return get_option($option);
}
endif;
/* END deprecated.php 
------------------------------------*/

/* Start plugin.php
------------------------------------*/
if (!function_exists('plugin_basename')):
function plugin_basename($file) {
	$file = str_replace('\\','/',$file); // sanitize for Win32 installs
	$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
	$plugin_dir = str_replace('\\','/',SLIMSTAT_WPROOT . 'wp-content/plugins'); // sanitize for Win32 installs
	$plugin_dir = preg_replace('|/+|','/', $plugin_dir); // remove any duplicate slash
	$file = preg_replace('|^' . preg_quote($plugin_dir, '|') . '/|','',$file); // get relative path from plugins dir
	return $file;
}
endif;
/* END plugin.php 
------------------------------------*/

/* CONTINUE wp-settings.php 
------------------------------------*/
if (function_exists('mb_internal_encoding')) {
	if (!@mb_internal_encoding(get_option('blog_charset')))
		mb_internal_encoding('UTF-8');
}

// If already slashed, strip.
if ( get_magic_quotes_gpc() ) {
	$_GET    = stripslashes_deep($_GET   );
	$_POST   = stripslashes_deep($_POST  );
	$_COOKIE = stripslashes_deep($_COOKIE);
}

// Escape with wpdb.
$_GET    = add_magic_quotes($_GET   );
$_POST   = add_magic_quotes($_POST  );
$_COOKIE = add_magic_quotes($_COOKIE);
$_SERVER = add_magic_quotes($_SERVER);

/* OTHERS 
------------------------------------*/
if (!function_exists('is_admin')):
function is_admin() {// TO DO
	global $wgUser;
	if ( ( function_exists('zp_loggedin') && zp_loggedin() ) || // zenphoto
			( is_object($wgUser) && is_a($wgUser, 'User') && $wgUser->isAllowed('protect') ) ) { // mediawiki
		return true;
	}
	$admin_check = array(// RegEx
			// zenphoto
			'/zen/', '/admin\.php','/admin$', '/admin/',
			// vanilla	
			'/settings\.php', '/people\.php', '/settings/', '/account/', '/people/',
			// mediawiki	
			'title=Special:Userlog', 'Special:Preferences', 'title=MediaWiki:[^\.]+\.css&', 'title=-&action=',
			// dokuwiki
			'(\?|&)do=login', '(\?|&)do=admin', '(\?|&)do=edit', '(\?|&)do=logout', '(\?|&)do=profile',
			// photostack
			'organize\.php',
			// phpbb
			'/profile.php', '/login.php', '/admin/'
		);
	foreach($admin_check as $admin){
		if (ereg($admin, $_SERVER['REQUEST_URI']))
			return true;
	}
	global $wgCookiePrefix, $board_config;
	if (isset($wgCookiePrefix) && isset( $_COOKIE[$wgCookiePrefix.'UserID'] )) { // mediawiki
		return true;
	} elseif (isset($board_config['cookie_name']) && isset($_COOKIE[$board_config['cookie_name'].'_data'])) { // PHPBB
		$phpbb_auth = unserialize(stripslashes($_COOKIE[$board_config['cookie_name'] . '_data']));
		if ($phpbb_auth['userid'] > -1 )
			return true;
	} elseif (isset($_COOKIE['DokuWiki'])) { // DokuWiki
		return true;
	}
	return false;
}
endif;

require_once ( SLIMSTATPATH . "wp-slimstat-ex-config.php" );
//$GLOBALS['SlimCfg'] =& $SlimCfg;
if (!isset($GLOBALS['slimtrack_ext']))
	$GLOBALS['slimtrack_ext'] =& $slimtrack_ext;
if ( $SlimCfg->geoip != 'mysql' ) {
	if ( !function_exists('geoip_country_code_by_name') )
		require_once(SLIMSTATPATH . 'lib/geoip/geoipcity.inc');
	require_once(SLIMSTATPATH . 'lib/geoip/geoipregionvars.php');
}
// using javascript?
if (isset($_GET['php_track'])) {
	$_SERVER["HTTP_REFERER"] = urldecode($_GET["ref"]);
	
	$url = @parse_url( urldecode($_GET["res"]) );
	if ( isset( $url["path"] ) ) {
		if ( isset( $url["query"] ) ) {
			$_SERVER["REQUEST_URI"] = $url["path"]."?".$url["query"];
		} else {
			$_SERVER["REQUEST_URI"] = $url["path"];
		}
	}
}
require_once ( SLIMSTATPATH . "lib/track.php" );
$SSTrack->slimtrack();

// destruct all.
$SlimCfg->geoip_close();
mysql_close($GLOBALS['wpdb']->dbh);
$GLOBALS['wpdb'] = $GLOBALS['SlimCfg'] = $GLOBALS['slimtrack_ext'] = $wpdb = $SlimCfg = $slimtrack_ext = $SSTrack = null;
unset($GLOBALS['wpdb'], $GLOBALS['SlimCfg'], $GLOBALS['slimtrack_ext'], $wpdb, $SlimCfg, $slimtrack_ext, $SSTrack);

}// End of sstrack_external

//if (!isset($_GET['php_track']))
	register_shutdown_function('sstrack_external');

/* External PHP program tracking END
---------------------------------------------------------------*/
?>