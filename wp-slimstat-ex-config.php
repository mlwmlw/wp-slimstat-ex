<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

define('SLIMSTAT_PINPATH',  SLIMSTATPATH . 'pins/');
define('SLIMSTAT_DOMAIN',  'wp-slimstat-ex');
define('SLIMSTAT_RESOURCE_COL', 1);
define('SLIMSTAT_DEFAULT_FILTER', '(1=1)');

if (!defined('SLIMSTAT_EXTERNAL_IPTC'))
	define('SLIMSTAT_EXTERNAL_IPTC', false); // set this true to use external ip-to-country database

if (!defined('SLIMSTAT_USER_AGENT'))
	define('SLIMSTAT_USER_AGENT', false); // set this true to save user-agent data.

class SlimCfg {
	var $version = '2.1.2'; // Current SlimStat-Ex version
	var $last_db_update_version = '2.1b1';

	var $tbPrefix, $current_table;
	var $table_stats, $table_feed, $table_resource, $table_dt, $table_pins, $table_ua, $table_sites;
	var $option, $exclude, $caps, $id2module;
	var $pluginURL, $basedir;
	var $bot_array, $get, $midnight_print, $midnight_db, $indexkey, $db_offset;

	var $geo, $geoip, $geo_pecl;
	var $is_wpmu, $wp_version;

	var $option_page = 'admin.php?page=wp-slimstat-ex-option';
	var $version_check_url = 'http://082net.com/update-check.php?check_plugin=wp-slimstat-ex';
	var $plugin_home = 'http://082net.com/tag/wp-slimstat-ex/?orderby=modified';
	var $package_url = 'http://082net.com/?dl=wp-slimstat-ex-plugin.zip';

	function SlimCfg() {
		$this->_init();
	}

	function _init() {
		$this->is_wpmu = $this->is_wpmu();
		$this->wp_version = $this->wp_version();

		$this->option = $this->get_options();
		$this->exclude = $this->get_exclusions();
		$this->caps = $this->get_caps();

		$this->basedir = dirname($this->_basename(__FILE__));	 // plugin folder name
		$this->pluginURL =  get_option('siteurl')."/wp-content/plugins/".$this->basedir;	 // Plugin URL(path)
		$this->get = $this->parse_GET(); // panel, filters, encoded filters
		$this->midnight_print = $this->mktime(array('h'=>0, 'i'=>0, 's'=>0), $this->time());
		$this->midnight_db = $this->time_switch($this->midnight_print, 'db');
		$this->db_offset = $this->get_db_offset();

		$this->init_geoip();
		$this->init_tables();
		$this->check_caps();
		$this->bot_array();
	}

	function init_tables() {
		if (defined('SLIMSTAT_EXTRACK')) { // if external tracking
			global $slimtrack_ext;
			$table_prefix = $slimtrack_ext['table_prefix'];
		} else 
			global $table_prefix;

		$this->tbPrefix = $table_prefix . "slimex_";
		$this->table_stats = $this->tbPrefix . "stats";	// common stats
		$this->table_feed = $this->tbPrefix . "feed";	 // feed stats
		$this->table_dt = $this->tbPrefix . "dt";	 // compressed hit, vists, uniques
		$this->table_pins = $this->tbPrefix . "pins";	// Pin information
		$this->table_resource = $this->tbPrefix . "resource"; // resource table
		if (SLIMSTAT_USER_AGENT === true) {
//			$this->table_sites = $this->tbPrefix . "sites"; // site table
			$this->table_ua = $this->tbPrefix . "ua"; // user agent table for dev.
		}
		$this->current_table = $this->select_table(); // Current table to select
	}

	function init_geoip() {
		$this->geo_pecl = function_exists('geoip_database_info');
		$this->geoip = $this->geoip();
		if (!$this->geoip) // could not found any GeoIP data file. we should install.
			$this->admin_notice('geoip');
	}

	function bot_array() {
		if (isset($this->bot_array))
			return;
		$this->bot_array['bots'] = array(12,43,45,46,47,48,49,50,51,52,53,107,108,109,110,111,121,135,136,159,174,175,176,177,180,182);
		$this->bot_array['feeds'] = array(13,14,15,20,44,54,55,56,57,61,91,95,96,97,98,99,100,130,131,132,133,134,137,138,139,140,141,142,143,144,146,147,148,149,150,151,152,154,156,157,162,164,165,166,167,168,169,170,171,172,173,178,179,183,184,1998);
		$this->bot_array['validators'] = array(16,17,18,93,123,124,125,126,127,128,129,1999);
		$this->bot_array['tools'] = array(19,22,40,41,72,73,77,80,85,58,59,60,145,155);
	}

	function plugins_loaded() {
		if (!$this->indexkey)
			$this->indexkey = array('common'=>$this->_getIndexKeys('common', true), 'feed'=>$this->_getIndexKeys('feed', true));
	}

	function admin_notice($what) {
		switch($what):
		case 'geoip':
		if ($this->wp_version < '2.8')// WP 2.8 or greater
			$message = __('GeoIP data is missing. You should upload <a href="http://www.maxmind.com/app/geoip_country" target="_blank">GeoIP.dat</a> file to <code>wp-slimstat-ex/lib/geoip/data/</code> folder manually.<br />On WP 2.8 or greater, SlimStat-Ex supports automated install or update GeoIP data file.', SLIMSTAT_DOMAIN);
		else
			$message = __('GeoIP data is missing. You can install it from <a href="admin.php?page=wp-slimstat-ex-admin">SlimStat-Admin</a> page.', SLIMSTAT_DOMAIN);
		break;
		default:
		break;
		endswitch;
		if (!empty($message) && function_exists('add_action'))
			add_action('admin_notices', create_function('$a', 'echo \'<div class="error"><p>'.$message.'</p></div>\';'));
	}

	function wp() {
	}

	function default_options() {
		return array('tracking'=>1, 'usepins'=>1, 'cachelimit'=>0, 'guesstitle'=>1, 'dbmaxage'=>0, 'limitrows'=>20, 'iptohost'=>0, 'whois'=>1, 'whois_db'=>'dnsstuff', 'meta'=>0, 'visit_type'=>'uniques', 'count_type'=>'hits', 'stats_type'=>'all', 'time_offset'=>0, 'use_ajax'=>1, 'ajax_history'=>0, 'nice_titles'=>1, 'ignore_bots'=>0, 'track_mode'=>'full', 'view_mode'=>'chart');
	}

	function default_exclusions() {
		return array('ignore_bots'=>0, 'ig_bots'=>0, 'ig_feeds'=>0, 'ig_validators'=>0, 'ig_tools'=>0, 'black_ua'=>'', 'white_ua'=>'', 'ignore_ip'=>'');
	}

	function default_caps() {
		return array( 
			'administrator'=>array('ignore_slimstat_track', 'view_slimstat_stats', 'manage_slimstat_options'),
			'editor'=>array('ignore_slimstat_track', 'view_slimstat_stats'),
			'author'=>array('ignore_slimstat_track', 'view_slimstat_stats'),
			'contributor'=>array('ignore_slimstat_track'),
			'subscriber'=>array('ignore_slimstat_track')
		);
	}

	function get_options() {
		$op = get_option('wp_slimstat_ex');
		if (is_array($op))
			return array_merge($this->default_options(), $op);
		return $this->default_options();
	}

	function get_exclusions() {
		$exclude = get_option('wp_slimstat_ex_exclude');
		if (is_array($exclude))
			return array_merge($this->default_exclusions(), $exclude);
		return $this->default_exclusions();
	}

	function get_caps() {
		$cap = get_option('wp_slimstat_ex_caps');
		if (is_array($cap) && !empty($cap))
			return array_merge($this->default_caps(), $cap);
		return $this->default_caps();
	}

	function get_sites($context='apply') {
		$sites = get_option('wp_slimstat_ex_sites');
		if ( !$sites || !is_array($sites) || empty($sites) ) {
			$sites = array(1=>array('host'=>'%wphost%', 'home'=>'%wphome%'));
			update_option('wp_slimstat_ex_sites', $sites);
		}
		if ($context != 'edit') {
			$sites[1]['home'] = get_option('home');
			$sites[1]['host'] = $this->get_url_info($sites[1]['home'], 'host');
		}
		return $sites;
	}

	function has_cap($cap='') {
		if (empty($cap))
			return false;
		return current_user_can($cap);
	}

	function check_caps($force = false) {
		if (defined('SLIMSTAT_EXTRACK'))
			return;
		$checked = get_option('wp_slimstat_ex_cap_checked');
		if (!$force && $checked)
			return;
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();

		$default_caps = array_values($this->caps['administrator']);
		foreach ($wp_roles->role_names as $rolekey => $role_name) {
			if (!isset($this->caps[$rolekey])) continue;
			foreach ($default_caps as $cap) {
				if ( in_array($cap, $this->caps[$rolekey]) && !$wp_roles->role_objects[$rolekey]->has_cap($cap) )
					$wp_roles->role_objects[$rolekey]->add_cap($cap);
				elseif ( !in_array($cap, $this->caps[$rolekey]) && $wp_roles->role_objects[$rolekey]->has_cap($cap) )
					$wp_roles->role_objects[$rolekey]->remove_cap($cap);
			}
		}
		update_option('wp_slimstat_ex_cap_checked', 1);
	}

	function _getIndexKeys($table = 'common', $deep = false) {
		global $wpdb;
		$_table = $this->string2table($table);
		$current_keys = array();
		$pre_len = array();
		$cur_len = array();
		$key_array = array('dt_total', 'resource_total', 'resource', 'searchterms', 'domain', 'referer', 'platform', 'browser', 'language', 'visit', 'country', 'remote_ip', 'dt', 'rs_string', 'rs_md5', 'rs_md5_site');
		if($myIndexStructure = $wpdb->get_results("SHOW INDEX FROM $_table", ARRAY_A)){
			foreach ( $myIndexStructure as $index_details ) {
				$key = $index_details['Key_name'];
				$col = $index_details['Column_name'];
				$len = $index_details['Cardinality'];
				$len = isset($pre_len[$key]) ? max($pre_len[$key], $len) : $len;
				$pre_len[$key] = $len;
				if(in_array($key, $key_array)) {
					if($deep) {
						$current_keys[$key]['column'][] = $col;
						$current_keys[$key]['length'] = $len;
					} else
						$current_keys[] = $key;
				}
			}
			return $current_keys;
		}
		return array();
	}

	function use_indexkey($mokey, $table='') {
		$table = ($table == '') ? $this->current_table : $table;
		$keys = ($table == $this->table_stats) ? $this->indexkey['common'] : $this->indexkey['feed'];
		$pkeys = array();
		if(isset($keys['remote_ip'])) $pkeys[] = 'remote_ip';
		if(isset($keys['resource'])) $pkeys[] = 'resource';
		if(isset($keys['referer'])) $pkeys[] = 'referer';
		if(isset($keys['domain'])) $pkeys[] = 'domain';
		if(isset($this->get['fd']) && isset($keys['dt'])) {// dt index will automatically applied
			if($mokey != 'dt' && in_array($mokey, $pkeys))
				return " USE INDEX (dt,{$mokey}) ";
			return " USE INDEX (dt)";
		}
//		if(!isset($this->get['ff']))// use default 
//			return "";
		$use_key = "";
		if(!isset($keys[$mokey]))
			$mokey = "";
		switch($this->get['ff']) {
			case 0:
				if(isset($keys['domain']))// varchar(255) index keys...
					return " USE INDEX (domain) ";
			break;
			case 1:
				if(isset($keys['searchterms']))// varchar(255) index keys...
					return " USE INDEX (searchterms) ";
			break;
			case 2:
				if(isset($keys['resource']))// This is primary key
					return " USE INDEX (resource) ";
			break;
			case 3:
				if(isset($keys['remote_ip']))// This is primary key
					return " USE INDEX (remote_ip) ";
				$use_key = 'remote_ip';
			break;
			case 4:
				$use_key = 'browser';
			break;
			case 5:
				$use_key = 'platform';
			break;
			case 6:
				$use_key = 'country';
			break;
			case 6:
				$use_key = 'language';
			break;
			default:
			break;
		}
		if(!isset($keys[$use_key]))
			$use_key = $mokey;
		if('' == $use_key)
			return "";
		return " USE INDEX (".(($keys[$mokey]['length'] > $keys[$use_key]['length']) ? $mokey : $use_key).") ";
	}

	// key length desc
	function indexkey_sort($a, $b) {
		if ($a['length'] == $b['length'])
			return 0;
		return ($a['length'] > $b['length']) ? -1 : 1;
	}

	function _basename($file) {
		return plugin_basename($file);
	}

	function _getWebPath($url="home") {
		return trim($this->get_url_info($url, 'path'), '/');
	}

	function get_url_info($url, $return='all') {
		switch ($url):
		case 'home': case 'siteurl':
			$url = get_option($url);
		break;
		default: break;
		endswitch;

		$parsed = @parse_url($url);
		if (!$parsed || !isset($parsed['host']))
			return $return == 'all' ? array() : '';

		switch ($return):
		case 'host': case 'path': case 'query': case 'fragment': case 'user': case 'pass': case 'port':
			return isset($parsed[$return]) ? $parsed[$return] : '';
		break;
		case 'all': default:
			return $parsed;
		break;
		endswitch;
	}

	function my_esc( $str = '' ) {
		global $wpdb;
		return $wpdb->escape($str);
	}

	function select_table() {
		$neededTable = ( $this->get['pn'] == 2 ) ? $this->table_feed : $this->table_stats;
		return $neededTable;
	}

	function string2table($table) {
		switch($table) {
			case 'feed':
				$_table = $this->table_feed;
			break;
			case 'dt':
				$_table = $this->table_dt;
			break;
			case 'pins':
				$_table = $this->table_pins;
			break;
			case 'resource':
				$_table = $this->table_resource;
			break;
			case 'common':
				$_table = $this->table_stats;
			break;
			case 'ua': case 'user_agent':
				$_table = $this->table_ua;
			break;
			default:
				$_table = $table;
			break;
		}
		return $_table;
	}

	function get_gmt_from_date($string) {
		return get_gmt_from_date($string);
	}

	function get_date_from_gmt($string) {
		return get_gmt_from_date($string);
	}

	function time($time=null, $db=0, $type='timestamp') {
		if (!$time)
			$time = time();

		$offset = get_option('gmt_offset') * 3600;
		switch ( $type ) {
			case 'mysql':
				return ( $db ) ? gmdate( 'Y-m-d H:i:s', $time ) : gmdate( 'Y-m-d H:i:s', ( $time + $offset ) );
				break;
			case 'timestamp':
				return ( $db ) ? $time : $time + $offset;// should be used with gmdate
				break;
		}
	}

	function strtotime($string, $for='print') {
		if (strpos($string, 'GMT') === false)
			$string .= ' GMT';
		$time = strtotime($string);// get GMT time
		if ($for == 'db')
			$time = $this->time_switch($time, 'db');
		return $time;
	}

	function mktime($args='', $time=null, $for='print') {
		$default = array('h'=>null, 'i'=>null, 's'=>null, 'm'=>null, 'd'=>null, 'y'=>null);
		$args = wp_parse_args($args, $default);
		$dt_formats = array('h'=>'G', 'i'=>'i', 's'=>'s', 'm'=>'n', 'd'=>'d', 'y'=>'Y');

		if (!$time)
			$time = $this->time();

		foreach($args as $t => $v) {
			$interval = 0;
			if (is_string($v) && ($v[0] == '+' || $v[0] == '-')) {
				$interval = intval($v);
				$v = null;
			}
			if (!isset($v)) {
				$args[$t] = gmdate($dt_formats[$t], $time) + $interval;
			}
		}
		extract($args, EXTR_SKIP);

		$new_dt = gmmktime( $h, $i, $s, $m, $d, $y );
		if ($for == 'db')
			$new_dt = $this->time_switch($new_dt, 'db');
		return $new_dt;
	}

	function time_switch($time, $to='db') {// time for printed with gmdate(), or on DB
		switch($to) {
			case 'db': case 'gmt':
			return $time - (get_option('gmt_offset') * 3600);
			break;
			case 'print': case 'blog':
			return $time + (get_option('gmt_offset') * 3600);
			break;
			default:
			return $time;
			break;
		}
	}

	function date($date_format, $time, $translate=true) {
		return mysql2date( $date_format, gmdate( 'Y-m-d H:i:s', $time ), $translate );
	}

	function date_str($format, $time=null, $current_y=false, $current_m=false, $current_d=false) {
		$_time = $this->time_switch($time, 'print');

		$y = $this->date('Y', $_time);
		$m = $this->date('m', $_time);
		$d = $this->date('d', $_time);

		if (false !== $current_y) {
			if ($current_y == $y)
				$format = str_replace(array('L', 'o', 'Y', 'y'), '', $format);
			else
				$current_y = $y;
		}
		if (false !== $current_m) {
			if ($current_m == $m)
				$format = str_replace(array('F', 'M', 'm', 'n', 't'), '', $format);
			else
				$current_m = $m;
		}
		if (false !== $current_d) {
			if ($current_d == $d)
				$format = str_replace(array('d', 'D', 'j', 'l', 'N', 'S', 'w', 'z'), '', $format);
			else
				$current_d = $d;
		}

		$format = trim($format, ' ,/-');
		$format = preg_replace('/^\W*(.*)?\W*$/', '\1', $format);

		if (0 === $current_y)
			$current_y = $y;
		if (0 === $current_m)
			$current_m = $m;
		if (0 === $current_d)
			$current_d = $d;

		return $this->date($format, $_time);
	}

	function parse_GET($filter_encode = true, $interval = true, $normal = true) {
		$get = array();
		$get['pn'] = isset($_GET['panel']) ? (int) $_GET['panel'] : 1;
		$f_interval = isset($_GET['fd']) && (strpos($_GET['fd'], "|") || stripos($_GET['fd'], "%7C")) ? trim(urldecode($_GET['fd'])) : null;

		if( $normal && isset($_GET['fi']) && '' !== $_GET['fi'] && isset($_GET['ff']) && isset($_GET['ft']) ) {
			$get['ff'] = (int)$_GET['ff'];
			$get['ft'] = (int)$_GET['ft'];
			$get['fi'] = stripslashes(urldecode($_GET['fi']));
		}

		if ( $interval && isset( $f_interval ) ) {
			$get['fd'] = array(0, 0);
			$intervals = explode( "|", $f_interval, 2 );
			$intervals[0] = (int)$intervals[0];
			$intervals[1] = (int)$intervals[1];
			if( max( $intervals ) > 0 && ($intervals[1] > $intervals[0]) ) {
				$get['fd'] = array( $intervals[0], $intervals[1] );
			}
		}

		$get['slim_table'] = isset($_GET['slim_table']) ? trim($_GET['slim_table']) : null;
		$get['view_mode'] = isset($_GET['view_mode']) && !empty($_GET['view_mode']) ? trim($_GET['view_mode']) : $this->option['view_mode'];
		if ($this->is_chart())
			$get['view_mode'] = 'chart';

		$get['slim_offset'] = isset($_GET['slim_offset']) ? (int)$_GET['slim_offset'] : 0;
		$get['slim_offset'] = ($get['slim_offset'] < 0) ? 0 : $get['slim_offset'];

		//re-encode filter values
		if($filter_encode) {
			$get['fi_encode'] = "";
			$get['fd_encode'] = "";
//			$get['offset_encode'] = "";
			if($normal && isset($get['fi'])) 
				$get['fi_encode'] = '&amp;fi='.urlencode($get['fi']).'&amp;ff='.$get['ff'].'&amp;ft='.$get['ft'];
			if ( $interval && isset($get['fd']) ) 
				$get['fd_encode'] = '&amp;fd='.$f_interval;
//			if ( $get['slim_table'] )
//				$get['fi_encode'] .= '&amp;slim_table='.$get['slim_table'];
//			if ( $get['slim_offset'] )
//				$get['offset_encode'] .= '&amp;slim_offset='.$get['slim_offset'];
		}

		return $get;
	}

	function get_db_offset($limit=false) {
		if (!$limit)
			$limit = $this->option['limitrows'];
		return $this->get['slim_offset'] * (int)$limit;
	}

	function is_chart() {
		return defined('SLIMSTAT_CHART_AJAX') && SLIMSTAT_CHART_AJAX === true;
	}

	function convert_encoding($str, $charset='') {
		if (!function_exists('mb_convert_encoding'))
			return $str;
		$charset = '' == $charset ? 'ASCII, UTF-8, EUC-KR, ISO-8859-1, JIS, EUC-JP, SJIS' : $charset;
		$str = mb_convert_encoding($str, get_option('blog_charset'), $charset );
		return $str;
	}

	function truncate($text, $len = 120) {
		if (function_exists('mb_strcut')) {
			$output = (strlen($text) >$len) ? mb_strcut($text, 0, $len, get_option('blog_charset')) . '...' : $text;
		} else {
			$output = (strlen($text) >$len) ? substr($text, 0, $len) . "..." : $text;
		}
		return $output;
	}

	function trimString($r, $length = 26) {
		$r = eregi_replace( "^http://", "", $r );
		$r = eregi_replace( "^www.", "", $r );
		$r = $this->truncate($r, $length);
		return $r;
	}

	function is_wpmu() {
		if(isset($this->is_wpmu))
			return $this->is_wpmu;
		global $wp_version, $wporg_version, $wpmu_version;
		if(strpos($wp_version, 'wordpress-mu') !== false)
			return true;
		if(isset($wporg_version) || isset($wpmu_version))
			return true;
		return false;
	}
	
	function wp_version() {
		if(isset($this->wp_version))
			return $this->wp_version;
		global $wp_version, $wporg_version, $wpmu_version;
		if(!$this->is_wpmu || isset($wpmu_version))
			return $wp_version;

		if(isset($wporg_version))
			return $wporg_version;
		// wpmu - increment version by 1.0 to match wp
		// borrowed from K2 theme (http://getk2.com)
		preg_match("/\d\.\d/i", $wp_version, $match);
		$match[0] = $match[0] + 1.0;
		return $match[0];
	}

	//borrowed from Extended Live Archives(http://www.sonsofskadi.net/extended-live-archive/)
	function version_check() {
		$remote = $this->remote_fopen($this->version_check_url);
		if(!$remote || strlen($remote) > 8 || 'error' == $remote) return -1;
		if ($remote > $this->version) return $remote; else return 0;
	}

	function remote_fopen($uri, $curl_force_post = false) {
		$timeout = 4;
		$parsed_url = @parse_url($uri);

		if ( !$parsed_url || !is_array($parsed_url) )
			return false;

		if ( !isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], array('http','https')) ) {
			$parsed_url['scheme'] = 'http';
			$uri = 'http://'.$uri;
		}

		if ( function_exists('curl_init') ) {// curl
			$handle = curl_init();
			curl_setopt ($handle, CURLOPT_URL, $uri);
			curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($handle, CURLOPT_TIMEOUT, $timeout);
			if($curl_force_post && isset($parsed_url['query'])) {
				curl_setopt($handle, CURLOPT_POST, true);
				curl_setopt($handle, CURLOPT_POSTFIELDS, $parsed_url['query']);
			}
			$buffer = curl_exec($handle);
			if (curl_errno($handle))
				return false;
			curl_close($handle);
			return $buffer;
		} else if ( ini_get('allow_url_fopen') ) {// fopen
			$fp = @fopen( $uri, 'r' );
			if ( !$fp )
				return false;
			//stream_set_timeout($fp, $timeout); // Requires php 4.3
			$linea = '';
			while( $remote_read = fread($fp, 4096) )
				$linea .= $remote_read;
			fclose($fp);
			return $linea;
		} else {// snoopy
			if(!class_exists('Snoopy')) 
				require(ABSPATH . 'wp-includes/class-snoopy.php');
			$client = new Snoopy();
			$client->_fp_timeout = $timeout;
			if (@$client->fetch($uri) === false)
				return false;
			return $client->results;
		}
	}

	function multi_remote_fopen($uris, $curl_force_post = false) {
		if (!function_exists('curl_multi_init'))
			return false;
		$chs = array();
		$info = array();
		$data = array();
		$count = count($uris);
		$mh = curl_multi_init();
		for ($i = 0; $i < $count; $i++) {
			$uri = $uris[$i];
			$parsed_url = @parse_url($uri);

			$chs[$i] = curl_init();
			curl_setopt ($chs[$i], CURLOPT_URL, $uri);
			curl_setopt ($chs[$i], CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt ($chs[$i], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($chs[$i], CURLOPT_TIMEOUT, 4);
			curl_setopt ($chs[$i], CURLOPT_HEADER, 0);
			if ( isset($parsec_url['query']) && $curl_force_post ) {
				curl_setopt ($chs[$i], CURLOPT_POST, true);
				curl_setopt ($chs[$i], CURLOPT_POSTFIELDS, $parsed_url['query']);
			}
			curl_multi_add_handle($mh, $chs[$i]);
		}

		$running=null;
		do {
			curl_multi_exec($mh, $running);
		} while ($running > 0);

		for ($i = 0; $i < $count; $i++) {
			$data[$i] = curl_multi_getcontent($chs[$i]);
			curl_multi_remove_handle($mh, $chs[$i]);
		}
		curl_multi_close($mh);
		return $data;
	}

	function check_user($redirect = true) {// for slimstat-admin only.
		auth_redirect();
		$has_cap = $this->has_cap('manage_options');
		$location = get_option('siteurl'). '/wp-admin/'.$this->option_page;
		if ( !$has_cap ) {
			if($redirect) {
				wp_redirect($location);
				exit();
			} else 
				return false;
		}
		return true;
	}

	function geoip() {
		$geoip = NULL;
		if ($this->geo_pecl) {// we don't need region ver, just country or city.
			if ( geoip_database_info(GEOIP_CITY_EDITION_REV0) || geoip_database_info(GEOIP_CITY_EDITION_REV1) )
				return 'city';
			return 'country';
		}

		$geo_dir = SLIMSTATPATH . "lib/geoip/data/";
		$geo_country = $geo_dir . "GeoIP.dat";
		$geo_city = $geo_dir . "GeoLiteCity.dat";

		if ( file_exists($geo_city) && is_readable($geo_city) )
			return 'city';
		if ( file_exists($geo_country) && is_readable($geo_country) )
			return 'country';
		return $geoip;
	}

	function geo_open() {
		if (!$this->geoip)
			return false;
		if (!$this->geo) {
			$geo_file = SLIMSTATPATH . 'lib/geoip/data/'.($this->geoip == 'city' ? 'GeoLiteCity':'GeoIP').'.dat';
			$this->geo = geoip_open($geo_file, GEOIP_STANDARD);
		}
		return $this->geo;
	}

	function geoip_close() {
		if ($this->geo)
			geoip_close($this->geo);
	}

	function geo_db_date() {
		if (!$this->geoip)
			return 0;
		$this->geo_open();
		if (preg_match('|^[A-Za-z0-9_-]+\s+(\d{8})\s+|i', $this->geo->dbinfo, $info))
			return $info[1];
		return 0;
	}

	function geoip_country($ip) {
		if ( !$ip || !$this->geoip )
			return '';
		if ( strpos($ip, '.') === false )
			$ip = long2ip($ip);
		if ($this->geo_pecl)
			$country_code = geoip_country_code_by_name($ip);
		else {
			if (!$this->geo)
				$this->geo_open();
			$country_code = geoip_country_code_by_addr($this->geo, $ip);
		}
		if (!$country_code)
			return "";
		return strtolower($country_code);
	}

	function geoip_location($ip) {
		global $GEOIP_REGION_NAME;
		if ( !$ip || strpos($this->geoip, 'city') === false )
			return '';
		if ( strpos($ip, '.') === false )
			$ip = long2ip($ip);
		if ($this->geo_pecl) {
			$loc = (object) geoip_record_by_name($ip);
		} else {
			if (!$this->geo)
				$this->geo_open();
			$loc = geoip_record_by_addr($this->geo, $ip);
		}
		if ($loc->country_code) {
			if ( $loc->city && !seems_utf8($loc->city) ) {
				$loc->city = $this->convert_encoding($loc->city);
			}
			if ( $loc->region )
				$loc->region_full = $GEOIP_REGION_NAME[$loc->country_code][$loc->region];
		}
		return $loc;
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SlimCfg();
		}
		return $instance[0];
	}
}

if(!isset($SlimCfg))
	$SlimCfg =& SlimCfg::get_instance();

$GLOBALS['SlimCfg'] =& $SlimCfg;
?>