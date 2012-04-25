<?php
/*
Track vistors
*/
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

class SSTrack {
	var $tracked = false;
	// Ignore same access(ip, resource, referer....) within xx millisecond
	// Please DO NOT CHAGNE THIS if you don't know exactly what it means
	var $track_interval = 0;// (1000 = 1sec) - disabled by default
	var $mobilefix = array('2' =>'1040', '27'=>'1033', '9'=>'1041');
	var $sites;

	function SSTrack() {
		$this->__construct();
	}

	function __construct() {
		global $SlimCfg;
		$this->track_interval = (int)$this->track_interval;
		$this->remote_addr = $this->get_remote_addr();
		$this->sites = $SlimCfg->get_sites();
	}

	function feed_track($req) {
		global $doing_rss;
		if (defined('SLIMSTAT_EXTRACK')) 
			return false;
		if ( is_feed() || $doing_rss || (isset($_GET['feed']) && !empty($_GET['feed'])) ) 
			return true;
		// for redirected feed and WP older versions that miss feed request
		if ( !preg_match('/(tag|search)\/(feed|rdf|rss|rss2|atom)\/?$/i', $req) && (
				preg_match('/\/(feed|rdf|rss|rss2|atom)\/?(feed|rdf|rss|rss2|atom)?\/?$/i', $req) ||
				preg_match('/\/wp-(feed|rdf|rss|rss2|atom|comments-rss2).php/i', $req) ) )
			return true;
		return false;
	}

	function is_ignored() {
		global $SlimCfg;
		if (!$SlimCfg->option['tracking']) 
			return true;
		if (defined('SLIMSTAT_EXTRACK')) { // if external tracking
			global $slimtrack_ext;
		}
		$wp_path = '/'.trailingslashit($SlimCfg->_getWebPath('siteurl'));
		$wp_path = preg_replace('|^[/]+|', '/', $wp_path);
		// Do not track the admin pages and direct access to plugin or theme folder(css, javascript(AJAX) loading).
		if ( ( function_exists('is_admin') && is_admin() ) || 
		    ( function_exists('is_404') && is_404() ) || 
				( function_exists('is_preview') && is_preview() ) ||
				( function_exists('is_robots') && is_robots() ) ||
			 ( !defined('SLIMSTAT_EXTRACK_JS') && strpos($_SERVER['PHP_SELF'], $wp_path.'wp-content/') === 0 ) ||
			 strpos($_SERVER['PHP_SELF'], $wp_path.'wp-admin') === 0 ||
			 strpos($_SERVER['PHP_SELF'], $wp_path.'wp-includes/') === 0 ||
			 strpos($_SERVER['PHP_SELF'], $wp_path.'wp-cron.php') === 0 ||// ignore Cron job
			 strpos($_SERVER['PHP_SELF'], $wp_path.'wp-app.php') === 0 ||// ignore AtomPub call
			 strpos($_SERVER["PHP_SELF"], $wp_path."xmlrpc.php") === 0 ||
			 strpos($_SERVER["PHP_SELF"], $wp_path."wp-register.php") === 0 ||
			 strpos($_SERVER["PHP_SELF"], $wp_path."wp-login.php") === 0 ) 
			return true;

		if (!defined('SLIMSTAT_EXTRACK')) {// external track does not supports WP USER
			if ($SlimCfg->has_cap('ignore_slimstat_track'))
				return true;
		}

		if ( $this->_checkIgnoreList($this->remote_addr) )
			return true;
		return false;
	}

	function slimtrack() {
		global $wpdb, $SlimCfg;

		// track visitor only once
		if ($this->tracked)
			return;
		$this->tracked = true;
		if ($this->is_ignored())
			return;

		$localsearch = !empty( $_GET['s'] ) ? urldecode(trim($_GET['s'])) : false;

		$stat = array();
		$stat["remote_ip"] = sprintf( "%u", ip2long( $this->remote_addr ) );
		$stat["language"]	= $this->_determineLanguage();
		$stat["country"]	= $this->_determineCountry( $this->remote_addr );
		$stat["referer"] = "";
		$stat["domain"] = "";
		$url = "";
		if ( isset( $_SERVER["HTTP_REFERER"] ) ) {
			$stat["referer"] = preg_replace( '|^https?://|i', '', $_SERVER["HTTP_REFERER"] );
			$_host = explode('/', $stat["referer"]);
			if ('' == $_host[0] || !preg_match('|^(([a-z0-9_]+):([a-z0-9-_]*)@)?(([a-z0-9_-]+\.)*)(([a-z0-9-]{2,})\.?)$|iU', $_host[0])) {
				$stat["referer"] = "";
			} elseif ( false === $url = @parse_url( 'http://' . $stat["referer"] )) {
				$stat["referer"] = "";
			} else {
				$stat["domain"] = isset($url["host"]) ? $url["host"] : "";
				$stat["referer"] = ('' == $stat["domain"]) ? '' : $stat["referer"];
			}
		}
		$stat["searchterms"] = $localsearch ? $localsearch : $this->_determineSearchTerms( $url );
		if ($localsearch) { // Mark the resource to remember that this is a 'local search'
			$stat["resource"] = '__localsearch__';
		} elseif ( isset( $_SERVER["REQUEST_URI"] ) ) {
			$stat["resource"] = $_SERVER["REQUEST_URI"];
		} elseif ( isset($_SERVER["SCRIPT_NAME"]) ) {
			$stat["resource"] = isset( $_SERVER["QUERY_STRING"]) ? $_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"] : $_SERVER["SCRIPT_NAME"];
		} else {
			$stat["resource"] = isset( $_SERVER["QUERY_STRING"] ) ? $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"] : $_SERVER["PHP_SELF"];
		}
		if (strpos($stat["resource"], '/') === 0) {
			// treat trailing or no-trailing slash as the same (save DB space)
			$stat["resource"] = '/'.trim($stat["resource"], '.:/');
		}
		$target_table = $this->feed_track($stat['resource']) ? $SlimCfg->table_feed : $SlimCfg->table_stats;
		$info = $this->_parseUserAgent( $_SERVER["HTTP_USER_AGENT"] );
		$stat["platform"] = $info["platform"];
		$stat["browser"] = $info["browser"];
		$stat["version"] = $info["version"];

		if (SLIMSTAT_USER_AGENT === true) {
//			if ( $this->is_bot($stat["browser"], $_SERVER["HTTP_USER_AGENT"], true) || $stat['browser'] == '-1' || !$stat['browser'] )
				$stat["user_agent"] = $this->insUA($_SERVER["HTTP_USER_AGENT"], $stat["browser"], $stat['platform']);// for dev
//			else 
//				$stat["user_agent"] = 0;
		}
		// ignore bots and unknown user_agent
		if ( ($SlimCfg->exclude['ignore_bots'] == 1 || $SlimCfg->exclude['ignore_bots'] == 3) 
				&& $this->is_bot($stat['browser'], $_SERVER["HTTP_USER_AGENT"]) ) {
			return;
		}
		$stat["dt"] = time();
		$stat["visit"] = $this->_determineVisit( $stat["remote_ip"], $stat["browser"], $stat["version"], $stat["platform"], $target_table, $stat["dt"] );

		// There was so may stripslashes and escape. 
		// First, strip all slashes since wordpress already fixed magic_quotes_gpc.
		$stat = array_map('stripslashes', $stat);

		// resource
		$stat["resource"] = $this->insResource($stat["resource"]);// incRescoure needs un-escaped value
		if ($stat["resource"] === false || $stat["resource"] == 0) {
			if (mysql_error($wpdb->dbh))
				$wpdb->print_error();
			return;
		}
		// escape values with $wpdb->escape
		// You should apply wp_specialchars($string, true) or attribute_escape($string) on output html (attribute values)
		$stat = add_magic_quotes($stat);

		if ($this->track_interval > 0) {
			$last_vist = $wpdb->get_row("SELECT * FROM $target_table 
					WHERE remote_ip = '{$stat['remote_ip']}'
					AND resource = '{$stat['resource']}'
					AND dt >= '".($stat['dt'] - $this->trac_interval)."'
					ORDER  BY dt desc LIMIT 1");
			if ($last_visit)
					return;
		}

		$myQuery = "INSERT INTO $target_table ( `" .
					implode( "`, `", array_keys( $stat ) ) .
					"` ) VALUES ( \"" .
					implode( "\", \"", array_values( $stat ) ) .
					"\" )";
		$insert_row = $wpdb->query($myQuery);
	}
	// end slimtrack

	function get_site_id($site_id=0, $url='', $force=false) {
		global $SlimCfg;
		$site_id = (int) $site_id;

		if ( !$force && !defined('SLIMSTAT_EXTRACK') )
			return $site_id;

		if ( 2 > $site_id )
			return 1;// default external site id.

		if ( !isset($this->sites[$site_id]) ) {// if user does not defined this external site.
			$scheme  = ( isset($_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
			if ('' == $url) {
				$host = $_SERVER['HTTP_HOST'];
			} else {
				$host = preg_replace('|^https?://|i', '', rtrim($url, '/'));
				if (strpos($host, '/') !== false) {// if $host is not host name but uri
					$host = $SlimCfg->get_url_info('http://'.$host, 'host');
					if ( !$host )
						return 1;// default external site id
				}
			}
			$this->sites[$site_id]['host'] = $host;
			$this->sites[$site_id]['home'] = $scheme . $host;
			update_option('wp_slimstat_ex_sites', $this->sites);
		}
		return $site_id;
	}

	function insResource($resource) {
		global $wpdb, $SlimCfg;
		$rs_esc = $wpdb->escape(trim($resource));
		$site_id = (defined('SLIMSTAT_SITE_ID')) ? SLIMSTAT_SITE_ID : 0;
		$site_id = $this->get_site_id($site_id);
		$query = "SELECT tr.id FROM $SlimCfg->table_resource tr WHERE tr.rs_md5 = MD5('{$rs_esc}') AND tr.site_id={$site_id} LIMIT 1";

		if ($_pre = $wpdb->get_row($query))
			return $_pre->id;

		$default = array('title'=>'', 'job'=>'', 'type'=>'');
		if ( defined('SLIMSTAT_EXTRACK') || !class_exists('SSFunction') )
			$_rstitle = array('job'=>'[external]', 'type'=>'');
		else
			$_rstitle = SSFunction::_guessPostTitle($resource, true);
		$_rstitle = array_merge($default, $_rstitle);
		$ins_query = "INSERT IGNORE INTO $SlimCfg->table_resource (rs_string, rs_md5, rs_title, rs_condition, site_id) 
			VALUES ('{$rs_esc}', MD5('{$rs_esc}'), '".$wpdb->escape($_rstitle['title'])."', '".$_rstitle['job'].$_rstitle['type']."', {$site_id}) ";
		if (false === $wpdb->query($ins_query))
			return false;
		return (int)$wpdb->get_var($query);
	}

	function insUA($ua, $browser='-1', $platform='-1') {
		global $wpdb, $SlimCfg;
		$ua_esc = $wpdb->escape(trim($ua));
		if ('' == $ua_esc)
			return 0;
		$query = "SELECT t.id FROM $SlimCfg->table_ua t WHERE t.ua_md5 = MD5('{$ua_esc}') LIMIT 1";
		if ($_pre = $wpdb->get_row($query))
			return $_pre->id;
		$ins_query = "INSERT IGNORE INTO $SlimCfg->table_ua (ua_string, ua_md5, browser, platform)
			VALUES ('{$ua_esc}', MD5('{$ua_esc}'), {$browser}, {$platform}) ";
		if (false === $wpdb->query($ins_query))
			return 0;
		return (int)$wpdb->get_var($query);
	}

	function _determineCountry( $ip='' ) {
		global $wpdb, $SlimCfg;
		if ( '' == $ip) $ip = $this->remote_addr;
		if (true === SLIMSTAT_EXTERNAL_IPTC)
			return SSTrack::_determineCountry_external($ip);

		return $SlimCfg->geoip_country($ip);
	}

	function _determineCountry_external($ip) {
		$coinfo = @file('http://www.hostip.info/api/get.html?ip=' . $ip);
		if ($coinfo) {
			if (preg_match('/Country:(.*?)\((\w*?)\)/isU', $coinfo[0], $match)) {
				$country = trim($match[2]);
				if ($country == "XX" || $country == "xx" || $country == "" || !$country)
					return "";
				return $country;
			}
		}
		return "";
	}

	function _determineLanguage() {
		global $SlimCfg;
		$myLangList = array(); 
		if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ) {
			preg_match( "/([^,;]*)/", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $myLangList );
			$l = str_replace( "_", "-", strtolower( $myLangList[0] ) );
			$l = $this->langoverlap($l);
			return $l;
		}
		return '';  // Indeterminable language
	}

	function langoverlap($lang) {
		if (empty($lang)) 
			return '';
		$langs = array("aa","ab","ae","af","ak","am","an","anp","ar","as","av","ay","az","ba","be","bg","bh","bi","bm","bn","bo","br","bs","ca","ce","ch","co","cr","cs","cu","cv","cy","da","de","dv","dz","ee","el","en","eo","es","et","eu","fa","ff","fi","fj","fo","fr","frr","fy","ga","gd","gl","gn","gu","gv","ha","he","hi","ho","hr","ht","hu","hy","hz","ia","id","ie","ig","ii","ik","in","io","is","it","iu","iw","ja","ji","jv","jw","ka","kg","ki","kj","kk","kl","km","kn","ko","kr","ks","ku","kv","kw","ky","la","lb","lg","li","ln","lo","lt","lu","lv","mg","mh","mi","mk","ml","mn","mo","mr","ms","mt","my","na","nb","nd","ne","ng","nl","nn","no","nr","nv","ny","oc","oj","om","or","os","pa","pi","pl","ps","pt","qu","rm","rn","ro","ru","rw","sa","sc","sd","se","sg","sh","si","sk","sl","sm","sn","so","sq","sr","ss","st","su","sv","sw","ta","te","tg","th","ti","tk","tl","tn","to","tr","ts","tt","tw","ty","ug","uk","ur","uz","ve","vi","vo","wa","wo","xh","yi","yo","za","zh","zu");
		$regions = array("ad","ae","af","ag","ai","al","am","an","ao","aq","ar","as","at","au","aw","ax","az","ba","bb","bd","be","bf","bg","bh","bi","bj","bm","bn","bo","br","bs","bt","bu","bv","bw","by","bz","ca","cc","cd","cf","cg","ch","ci","ck","cl","cm","cn","co","cr","cs","cu","cv","cx","cy","cz","dd","de","dj","dk","dm","do","dz","ec","ee","eg","eh","er","es","et","fi","fj","fk","fm","fo","fr","fx","ga","gb","gd","ge","gf","gg","gh","gi","gl","gm","gn","gp","gq","gr","gs","gt","gu","gw","gy","hk","hm","hn","hr","ht","hu","id","ie","il","im","in","io","iq","ir","is","it","je","jm","jo","jp","ke","kg","kh","ki","km","kn","kp","kr","kw","ky","kz","la","lb","lc","li","lk","lr","ls","lt","lu","lv","ly","ma","mc","md","me","mg","mh","mk","ml","mm","mn","mo","mp","mq","mr","ms","mt","mu","mv","mw","mx","my","mz","na","nc","ne","nf","ng","ni","nl","no","np","nr","nt","nu","nz","om","pa","pe","pf","pg","ph","pk","pl","pm","pn","pr","ps","pt","pw","py","qa","qm..qz","re","ro","rs","ru","rw","sa","sb","sc","sd","se","sg","sh","si","sj","sk","sl","sm","sn","so","sr","st","su","sv","sy","sz","tc","td","tf","tg","th","tj","tk","tl","tm","tn","to","tp","tr","tt","tv","tw","tz","ua","ug","uk","um","us","uy","uz","va","vc","ve","vg","vi","vn","vu","wf","ws","yd","ye","yt","yu","za","zm","zr","zw");
		$langoverlap = array("ie-ee"=>"","ko-kr"=>"ko","zh-ch"=>"zh-cn","ja-jp"=>"ja","english"=>"en","*"=>"");
		$lang = (isset($langoverlap[$lang]))?$langoverlap[$lang]:$lang;
		$l_split = split('-', $lang);
		$c = count($l_split);
		if (!in_array($l_split[0], $langs))
			return '';
		if ($c < 2)
			return $l_split[0];
		$last = $c-1;
		if (in_array($l_split[$last], $regions))
			return $l_split[0].'-'.$l_split[$last];
		return $l_split[0];
	}

	function _determineSearchTerms( $url = '' ) { 
		global $SlimCfg;
		// check url
		if (empty($url))
			return "";
		if ( !is_array( $url ) ) $myUrl = @parse_url( $url );
		else $myUrl = $url;
		if ( !isset($myUrl["host"]) || !isset($myUrl["query"]) )
			return "";

		// Host regexp, query portion containing search terms
		$sniffs = array( 
			array( "/google\./i", "q" ),
			array( "/alltheweb\./i", "q" ),
			array( "/yahoo\./i", "p" ),
			array( "/search\.aol\./i", "query" ),
			array( "/search\.looksmart\./i", "p" ),
			array( "/gigablast\./i", "q" ),
			array( "/s\.teoma\./i", "q" ),
			array( "/clusty\./i", "query" ),
			array( "/yandex\./i", "text" ),
			array( "/rambler\./i", "words" ),
			array( "/aport\./i", "r" ),
			array( "/search\.naver\./i", "query" ),
			array( "/search\.cs\./i", "query" ),
			array( "/search\.netscape\./i", "query" ),
			array( "/hotbot\./i", "query" ),
			array( "/search\.msn\./i", "q" ),
			array( "/altavista\./i", "q" ),
			array( "/web\.ask\./i", "q" ),
			array( "/search\.wanadoo\./i", "q" ),
			array( "/www\.bbc\./i", "q" ),
			array( "/tesco\.net/i", "q" ),
			array( "/bing\.com/i", "q" ),
			array( "/.*/", "search" ),
			array( "/.*/", "query" ),
			array( "/.*/", "q" )
		);
		foreach ( $sniffs as $sniff ) {
			if ( preg_match( $sniff[0], $myUrl["host"] ) ) {
				parse_str( $myUrl["query"], $q );
				if ( !isset($q[$sniff[1]]) )
					continue;
				$mySearchTerms = urldecode($q[$sniff[1]]);
				// get search string from google cached page addr
				if ($sniff[1] == 'q' && strpos($mySearchTerms, 'cache:') === 0) {
					$temp_str = explode('+', $mySearchTerms);
					unset($temp_str[0]);
					$mySearchTerms = implode(' ', $temp_str);
				}
				// Convert international encodings to UTF-8 only when blog charset is UTF-8.
				$blog_charset = strtoupper( get_option('blog_charset') );
				if ( $blog_charset == 'UTF-8' && !seems_utf8($mySearchTerms) ) { 
					if (strpos($mySearchTerms, '%u') === 0) { // UTF16-LE
						$mySearchTerms = $this->urlutfchr($mySearchTerms);
					}
					$mySearchTerms = $SlimCfg->convert_encoding($mySearchTerms);
				}
				return stripslashes($mySearchTerms);
			}
		}
		return "";
	}
	
	function _parseUserAgent( $ua = '' ) {
		$_m = array();
		$info = array(
			'platform' => '-1',
			'browser'  => '-1',
			'version'  => '',
//			'majorver' => '',
//			'minorver' => ''
		);
		if (empty($ua))
			return $info;
		$_ua = strtolower( $ua );
		$_ua = str_replace("funwebproducts", "", $_ua);

		// Browser type
		// Browser to OS 
		// Mobile Browser ID :: bigger than 1000, 1000 = misc moblie browser
		// Generic Bots :: 34, Web Downloaders(getright, flashget...) :: 2000
		$info = $this->_determineBrowser($_ua, $info);

		if ($info['browser'] > '999' && $info['browser'] < '1900') {
			$info['platform'] = $this->_determineMobileOS($_ua);// Mobile browsers
		}

		switch($info['browser']) {
			case '28':case '95':case '100':case '158':
				$info['platform'] = $this->_determineMacOS($_ua);// Mac only browsers
			break;
			case '30':case '32':case '36':case '36':case '37':
				$info['platform'] = $this->_determineUnixOS($_ua);// Unix only browsers
			break;
			default:break;
		}

		/* Platform */// Mobile OS ID :: bigger than 70
		if ($info['platform'] == '-1') { // If not defiened by browser
			if ( preg_match( '/([^dar]win[dows]*)[\s]?([0-9a-z]*)[\w\s]?([a-z0-9.]*)/i', $_ua, $_m ) ) {
				// Analyze weird Microsoft user agent
				$info['platform'] = $this->_determineWinOS($_ua, $_m);
			} elseif ( preg_match( '/(macintosh|mac_powerpc|ppc mac os|intel mac os|mac os x|darwin)/i', $_ua ) ) {
				$info['platform'] = $this->_determineMacOS($_ua);
			} else {
				$info['platform'] = $this->_determineMobileOS($_ua);
				// Other Unix OS and rest.
				$info['platform'] = ($info['platform'] == '-1') ? $this->_determineUnixOS($_ua) : $info['platform'];
			}
		}
		return $info;
	}

	function parse_browser($sniffs, &$info, $_ua='') {
		$desk2mobile = array('2' =>'1040', '27'=>'1033', '9'=>'1041');
		$is_mobile = false;

		// already determined brower but if it's mobile
		if ( ($info['browser'] > '999' && $info['browser'] < '1900') || $info['platform'] > '69' )
			$is_mobile = true;
		elseif ($info['browser'] != '-1')
			return;

		foreach ( $sniffs as $sniff ) {
			if ( strpos( $_ua, $sniff[0] ) === false )
				continue;
			if ( $is_mobile && isset($this->mobilefix[$sniff[1]]) )
				$info['browser'] = $this->mobilefix[$sniff[1]];
			else
				$info['browser'] = $sniff[1];
			if ( $sniff[2] != '' ) {
				if ( preg_match( '#'.$sniff[2].'#', $_ua, $_m ) || ereg( $sniff[2], $_ua, $_m ) ) {// first preg_match after ereg
					$info['version'] = $_m[ $sniff[3] ];
				} else {
					$info['version'] = '';
				}
			} else {
				$info['version'] = $sniff[3];
			}
			if($info['browser'] == '190' || $info['browser'] == '191') {
				mb_eregi( "^([0-9]*).(.*)$", $info['version'], $v );
				$info['version'] = $v[1];
			}
			if ( sizeof( $sniff ) == 5 && $info['platform'] == '-1' ) {
				$info['platform'] = $sniff[4];
			}
			break;// we don't have to check anymore.
		}
	}

	// lower case user-agent only
	function _determineBrowser($_ua, $info = array('platform'=>'-1', 'browser'=>'-1', 'version'=>'', 'majorver'=>'', 'minorver'=>'') ) {
		// User defined browsers: name regexp, browser id, version regexp, version match, platform (optional)

		// check if it's connection from mobile device first.
		$info['platform'] = $this->_determineMobileOS($_ua);
		$this->_determineMobileBrowser($_ua, &$info);

		$sniffs = array(
			array( 'netscape', '1', 'netscape[0-9]?/([[:digit:]\.]+)', 1 ),
			array( 'chrome', '190', 'chrome/([[:digit:]\.]+)', 1 ),
			array( 'chromium', '191', 'chromium/([[:digit:]\.]+)', 1 ),
			array( 'safari', '2', 'safari/([[:digit:]\.]+)', 1, '10' ),
			array( 'icab', '3', 'icab/([[:digit:]\.]+)', 1,  '10' ),
			array( 'firebird', '5', 'firebird/([[:digit:]\.]+)', 1 ),
			array( 'phoenix', '6', 'phoenix/([[:digit:]\.]+)', 1 ),
			array( 'camino', '7', 'camino/([[:digit:]\.]+)', 1, '10' ),
			array( 'chimera', '8', 'chimera/([[:digit:]\.]+)', 1, '10' ),
			array( 'msn explorer', '10', 'msn explorer ([[:digit:]\.]+)', 1 ),
			array( 'wordpress/', '11', 'wordpress/([[:digit:]\.]+)', 1 ),
			array( 'blogsearch', '12', 'blogsearch/([[:digit:]\.]+)', 1 ),
			array( 'allblog.net', '13', 'allblog.net ([[:digit:]\.]+)', 1 ),
			array( 'hanrss', '14', 'hanrss/([[:digit:]\.]+)', 1 ),
			array( 'xml-rpc.net', '15', 'xml-rpc.net ([[:digit:]\.]+)', 1 ),
			array( 'w3c_validator', '16', 'w3c_validator/([[:digit:]\.]+)', 1 ),
			array( 'w3clinemode', '16', 'w3clinemode/([[:digit:]\.]+)', 1),// same as above
			array( 'feedvalidator', '17', 'feedvalidator/([[:digit:]\.]+)', 1 ),
			array( 'jigsaw', '18', 'jigsaw/([[:digit:]\.]+)', 1 ),
			array( 'python-urllib', '19', 'python-urllib/([[:digit:]\.]+)', 1 ),
			array( 'newsgatoronline', '20', '', 0 ),
			array( 'newsgator', '20', 'newsgator/([[:digit:]\.]+)', 1 ),// same as above
			array( '(compatible; google desktop', '21', '', ''),// google search appliance
			array( 'java', '22', 'java/([[:digit:]\.]+)', 1 ),
			array( 'aol', '23', 'aol ([[:digit:]\.]+)', 1 ),
			array( 'america online browser', '24', 'america online browser ([[:digit:]\.]+)', 1 ),
			array( 'k-meleon', '25', 'k-meleon/([[:digit:]\.]+)', 1 ),
			array( 'kmeleon', '25', 'kmeleon/([[:digit:]\.]+)', 1 ),// same as above
			array( 'beonex', '26', 'beonex/([[:digit:]\.]+)', 1 ),
			array( 'opera', '27', 'opera( |/)([[:digit:]\.]+)', 2 ),
			array( 'omniweb', '28', 'omniweb/v([[:digit:]\.]+)', 1 ),
			array( 'konqueror', '29', 'konqueror/([[:digit:]\.]+)', 1, '20' ),
			array( 'galeon', '30', 'galeon/([[:digit:]\.]+)', 1 ),
			array( 'epiphany', '31', 'epiphany/([[:digit:]\.]+)', 1 ),
			array( 'kazehakase', '32', 'kazehakase/([[:digit:]\.]+)', 1 ),
			array( 'amaya', '33', 'amaya/([[:digit:]\.]+)', 1 ),
			// 34 is below there (bot or crawler)
			array( 'lynx', '35', 'lynx/([[:digit:]\.]+)', 1 ),
			array( 'links', '36', '\(([[:digit:]\.]+)', 1 ),
			array( 'elinks', '37', 'elinks[/ ]([[:digit:]\.]+)', 1 ),
			array( 'thunderbird', '38', 'thunderbird/([[:digit:]\.]+)',  1 ),
			array( 'flock', '39', 'flock/([[:digit:]\.]+)',  1 ),
			array( 'lwp-request', '40', 'lwp-request/(.*)$', 1 ),
			array( 'apachebench', '41', 'apachebench/(.*)$', 1 ),
			array( 'seamonkey', '42', 'seamonkey/([[:digit:]\.]+)', 1 ),
			array( 'mediapartners-google', '43', 'mediapartners-google/([[:digit:]\.]+)', 1 ),
			array( 'feedfetcher-google', '44', '', 0 ),
			// Google
			array( 'googlebot-image', '108', '', '' ),// google related.
			array( 'adsbot-google', '108', '', '' ),// google related.
			array( 'google-sitemaps', '108', '', '' ),// google related.
			array( 'googlebot-urlconsole', '108', '', '' ),// google related.
			array( 'googlebot/test', '108', '', '' ),// google related.
			array( 'mediapartners-google/', '109', '', ''),// google appliance
			array( 'gsa-crawler', '109', '', ''),// google search appliance
			array( '(compatible; googletoolbar', '109', '', ''),// google search appliance
			array( 'googlebot/', '45', 'googlebot/([[:digit:]\.]+)', 1 ),

			array( 'lanshanbot/', '121', '', ''),// msn bots.
			array( ' ms search ', '121', '', ''),// msn bots.
			array( 'msnbot-media', '121', '', ''),// msn bots.
			array( 'msnbot-news', '121', '', ''),// msn bots.
			array( 'msnbot-newsblogs', '121', '', ''),// msn bots.
			array( 'msnbot-products', '121', '', ''),// msn bots.
			array( 'msnbot/', '46', 'msnbot/([[:digit:]\.]+)', 1 ),

			array( 'yahoo-blogs', '47', 'yahoo-blogs/([[:digit:]\.]+)', 1 ),
			array( 'gigabot', '48', 'gigabot/([[:digit:]\.]+)', 1 ),
			array( 'zyborg', '49', 'zyborg/([[:digit:]\.]+)', 1 ),
			array( 'nutchcvs', '50', 'nutchcvs/([[:digit:]\.]+)', 1 ),
			array( 'ichiro', '51', 'ichiro/([[:digit:]\.]+)', 1 ),
			array( 'technoratibot', '52', 'technoratibot/([[:digit:]\.]+)', 1 ),
			array( 'heritrix', '53', 'heritrix/([[:digit:]\.]+)', 1 ),
			array( 'feedburner', '54', 'feedburner/([[:digit:]\.]+)', 1 ),
			array( 'feedpath', '55', 'feedpath/([[:digit:]\.]+)', 1 ),
			array( 'netvibes', '56', 'netvibes/([[:digit:]\.]+)', 1 ),
			array( 'greatnews', '57', 'greatnews/([[:digit:]\.]+)', 1 ),
			array( 'magpierss', '58', 'magpierss/([[:digit:]\.]+)', 1 ),
			array( 'livedoor feedfetcher', '59', 'livedoor feedfetcher/([[:digit:]\.]+)', 1 ),
			array( 'livedoor httpclient', '59', 'livedoor httpclient/([[:digit:]\.]+)', 1),// just treat as the same :-|
			array( 'snoopy', '60', 'snoopy v([[:digit:]\.]+)', 1 ),
			array( ' eolin', '61', '', 0 ),
			// Since 1.5
			array( 'mac finder', '62', 'mac finder ([[:digit:]\.]+)', 1, '12'),
			array( 'movabletype', '63', 'movabletype[/ ]([[:digit:]\.]+)', 1),
			array( 'typepad', '64', 'typepad[/ ]([[:digit:]\.]+)', 1),
			array( 'drupal', '65', '', ''),
			array( 'anonymouse', '66', '', ''),
			array( 'bluefish', '67', 'bluefish ([[:digit:]\.]+)', 1),
			array( 'amiga-aweb', '68', 'amiga-aweb/([[:digit:]\.]+)', 1),
			array( 'avantbrowser.com', '69', '', ''),
			array( 'amigavoyager', '70', 'amigavoyager/([[:digit:]\.]+)', 1),
			array( 'emacs-w3', '71', 'emacs-w3/([[:digit:]\.]+)', 1),
			array( 'curl', '72', 'curl/([[:digit:]\.]+)', 1),
			array( 'democracy', '73', 'democracy/([[:digit:]\.]+)', 1),
			array( 'dillo', '74', 'dillo/([[:digit:]\.]+)', 1),
			array( 'doczilla', '75', 'doczilla/([[:digit:]\.]+)', 1),
			array( 'edbrowse', '76', 'edbrowse/([[:digit:]\.]+)', 1),
			array( 'libfetch', '77', 'libfetch/([[:digit:]\.]+)', 1),//Fetch
			array( 'iceweasel', '78', 'iceweasel/([[:digit:]\.]+)', 1),//Gnuzilla and IceWeasel
			array( 'ibrowse', '79', 'ibrowse/([[:digit:]\.]+)', 1),
			array( 'ice browser', '80', 'ice browser/([[:digit:]\.]+)', 1),
//			array( 'danger hiptop', '81', '', ''), => moved to mobile
			array( 'kkman', '82', 'kkman([[:digit:]\.]+)', 1),
			array( 'mosaic', '83', 'mosaic/([[:digit:]\.]+)', 1),
			array( 'netpositive', '84', 'netpositive/([[:digit:]\.]+)', 1, '45'),
			array( 'songbird', '85', 'songbird/([[:digit:]\.]+)', 1),
			array( 'sylera', '86', 'sylera/([[:digit:]\.]+)', 1),
			array( 'shiira', '87', 'shiira/([[:digit:]\.]+)', 1, '10'),
			array( 'webtv', '88', 'webtv/([[:digit:]\.]+)', 1),//WebTV(MS)
			array( 'w3m/', '89', 'w3m/([[:digit:]\.]+)', 1),
			array( 'historyhound', '90', '', ''),
			array( 'blogkorea', '91', 'blogkorea/([[:digit:]\.]+)', 1 ),
			array( 'oregano', '92', 'oregano ([[:digit:]\.]+)', 1),
			array( 'wdg_validator', '93', 'wdg_validator/([[:digit:]\.]+)', 1),
			array( 'docomo', '94', 'docomo/([[:digit:]\.]+)', 1),
			array( 'newsfire', '95', 'newsfire/([[:digit:]\.]+)', 1),
			array( 'newsalloy', '96', 'newsalloy/([[:digit:]\.]+)', 1),
			array( 'liferea', '97', 'liferea/([[:digit:]\.]+)', 1),
			array( 'hatena rss', '98', 'hatena rss/([[:digit:]\.]+)', 1),
			array( 'feedshow', '99', 'feedshow/([[:digit:]\.]+)', 1),
			array( 'feedshowonline', '99', '', ''),// same as above
			array( 'netnewswire', '100', 'netnewswire/([[:digit:]\.]+)', 1),
			array( 'acorn browse', '101', 'acorn browse ([[:digit:]\.]+)', 1),
			// user agent from firestats (http://firestats.cc/)
			array( 'bonecho', '102', 'bonecho/([[:digit:]\.]+)', 1),
			array( 'lycos', '103', '', ''),
			array( 'multizilla ', '104', 'multizilla v([[:digit:]\.]+)', 1),
			array( 'multizilla', '104', 'multizilla/v?([[:digit:]\.]+)', 1),// same as above
			array( 'firefox', '4', 'firefox/([[:digit:]\.]+)',  1 ),// check after brother
			array( 'j2me', '105', '', ''),// j2me/midp browser
			array( 'midp', '105', '', ''),// j2me/midp browser
			array( 'php', '106', '', ''),
			// Inktomi & Yahoo
			array( 'slurp/si', '111', '', ''),// inktomi bots
			array( 'slurp/cat', '111', '', ''),// inktomi bots
			array( 'scooter/', '111', '', ''),// inktomi bots
			array( 'y!j-', '111', '', ''),// inktomi bots
			array( 'yahoo japan; for robot', '111', '', ''),// inktomi bots
			array( 'yahooseeker', '110', 'yahooseeker/([[:digit:]\.]+)', 1),
			array( 'yahoo pipes', '110', '', ''),// yahoo bots
			array( 'yahoo mindset', '110', '', ''),// yahoo bots
			array( 'yahoo! mindset', '110', '', ''),// yahoo bots
			array( 'yahoo-', '110', '', ''),// yahoo bots
			array( 'yahooysmcm', '110', '', ''),// yahoo bots
			array( 'yrl_odp_crawler', '110', '', ''),// yahoo bots
			array( 'yahoovideosearch', '110', '', ''),// yahoo bots
			array( 'yahoo! slurp/site', '110', '', ''),// yahoo bots
			array( 'yahooysmcm', '110', '', ''),// yahoo bots
			array( 'y!j', '110', '', ''),// yahoo bots
			array( 'yahoo! slurp', '107', '', ''),// yahoo main bot
			array( 'yahoo! de slurp', '107', '', ''),// yahoo main bot
			array( 'slurp', '111', '', ''),// inktomi bots
			array( 'cheshire', '112', 'cheshire/([[:digit:]\.]+)', 1, '10'),
			array( 'crazy browser', '113', 'crazy browser ([[:digit:]\.]+)', 1),
			array( 'enigma browser', '114', '', ''),
			array( 'granparadiso', '115', 'granparadiso/([[:digit:]\.]+)', 1),
			array( 'iceape', '116', 'iceape/([[:digit:]\.]+)', 1),
			array( 'k-ninja', '117', 'k-ninja/([[:digit:]\.]+)', 1),
			array( 'maxthon', '118', 'maxthon ([[:digit:]\.]+)', 1),
			array( 'minefield', '119', 'minefield/([[:digit:]\.]+)', 1),
			array( 'myie2', '120', '', ''),

			array( 'wii libnup', '122', 'wii libnup/([[:digit:]\.]+)', 1),
			array( 'w3c-checklink', '123', 'w3c-checklink/([[:digit:]\.]+)', 1),
			array( 'xenu link sleuth', '124', 'xenu link sleuth ([[:digit:]\.]+)', 1),
			array( 'cse html validator', '125', '', ''),
			array( 'csscheck', '126', 'csscheck/([[:digit:]\.]+)', 1),
			array( 'cynthia', '127', 'cynthia ([[:digit:]\.]+)', 1),
			array( 'htmlparser', '128', 'htmlparser/([[:digit:]\.]+)', 1),
			array( 'p3p validator', '129', '', ''),
			array( 'gregarius', '130', 'gregarius/([[:digit:]\.]+)', 1),
			array( 'bloglines', '131', 'bloglines/([[:digit:]\.]+)', 1),
			array( 'everyfeed-spider', '132', 'everyfeed-spider/([[:digit:]\.]+)', 1),
			array( '!susie', '133', '', ''),
			array( 'cocoal.icio.us', '134', 'bonecho/([[:digit:]\.]+)', 1),
			array( 'domainsdb.net metacrawler', '135', 'domainsdb\.net metacrawler v([[:digit:]\.]+)', 1),
			array( 'gsitecrawler', '136', 'gsitecrawler/([[:digit:]\.]+)', 1),
			array( 'feeddemon', '137', 'feeddemon/([[:digit:]\.]+)', 1),
			array( 'zhuaxia', '138', 'zhuaxia/([[:digit:]\.]+)', 1),
			array( 'akregator', '139', 'akregator/([[:digit:]\.]+)', 1),
			array( 'applesyndication', '140', 'applesyndication/([[:digit:]\.]+)', 1, '10'),
			array( 'blog conversation project', '141', '', ''),
			array( 'bottomfeeder', '142', '', ''),
			array( 'jetbrains omea reader', '143', 'jetbrains omea reader ([[:digit:]\.]+)', 1),
			array( 'ping.blo.gs', '144', 'ping.blo.gs/([[:digit:]\.]+)', 1),
			array( 'raggle', '145', 'raggle/([[:digit:]\.]+)', 1),
			array( 'rssbandit', '146', 'rssbandit ([[:digit:]\.]+)', 1),
			array( 'sharpreader', '147', 'sharpreader/([[:digit:]\.]+)', 1),
			array( 'yahoofeedseeker', '148', 'yahoofeedseeker/([[:digit:]\.]+)', 1),
			array( 'rojo', '149', 'rojo ([[:digit:]\.]+)', 1),
			array( 'kb.rmail', '150', '', ''),
			array( '(sage)', '151', '', ''),
			array( 'daum rss robot', '152', '', ''),
			array( 'thunderbird', '153', 'thunderbird/([[:digit:]\.]+)', 1),
			array( 'windows-rss-platform', '154', 'windows-rss-platform/([[:digit:]\.]+)', 1),
			array( 'universalfeedparser', '155', 'universalfeedparser/([[:digit:]\.]+)', 1),
			array( 'livejournal.com', '156', '', ''),
			array( 'vienna', '157', 'vienna/([[:digit:]\.]+)', 1, '10'),
			array( 'itunes', '158', 'itunes/([[:digit:]\.]+)', 1),
			array( 'quicktime', '159', 'qtver=([[:digit:]\.]+)', 1),
			array( 'realplayer', '160', '', ''),
			array( 'webindexer', '161', '', ''),
			array( 'xmind/xmind', '162', 'xmind/xmind-?([[:digit:]\.]+)', 1),
			array( 'hp web printsmart', '163', 'hp web printsmart [a-z0-9]*? ([[:digit:]\.]+)', 1),
			array( 'plagger/', '164', 'plagger/([[:digit:]\.]+)', 1),
			array( 'blogbridge', '165', '', ''),
			array( 'fastladder', '166', '', ''),
			array( 'newslife', '167', '', ''),
			array( 'rssowl', '168', '', ''),
			array( 'yeonmo', '169', '', ''),
			array( 'rmom', '170', '', ''),
			array( 'feedonfeeds', '171', '', ''),
			array( 'technoratisnoop', '172', '', ''),
			array( 'cazoodlebot', '173', '', ''),
			array( 'snapbot', '174', '', ''),
			array( 'ucla cs dept', '175', '', ''),
			array( 'httpclientbox', '176', '', ''),
			array( 'onnet-openapi', '177', '', ''),
			array( 's20 wing', '178', '', ''),
			array( 'openmaru feed aggregator', '179', '', ''),
			array( 'webscouter', '180', '', ''),
			// OpenID relative
			array( '-openid', '181', '', ''),
			array( ' openid', '181', '', ''),
			array( 'openod-', '181', '', ''),

			array( 'rebi-shoveler', '182', '', ''),
			array( 'mixsh rsssync', '183', '', ''),
			array( 'feedwave', '184', 'feedwave/([[:digit:]\.]+)', 1),

			array( 'msie ', '9', 'msie ([[:digit:]\.]+)', 1 )// check at the end
		);

		$this->parse_browser($sniffs, &$info, $_ua);

		// IE 8 compat mode
		if ( $info['browser'] == '9' && $info['version'] < '8.0' && strpos($_ua, 'trident/4.0') !== false )
			$info['version'] = '8.0';

		// Safari uses a strange versioning system
		if ( $info['browser'] == '2' || $info['browser'] == '1040' ) {
//			$ver = explode('.', trim($info['version']));
			$ver = trim($info['version']);
			if ($ver > '540')
				$info['version'] = '';// not released yet 2009.09.24
			elseif ($ver > '526')
				$info['version'] = '4.0.x';
			elseif ($ver >= '525.26')
				$info['version'] = '3.2.x';
			elseif ($ver >= '525.13')
				$info['version'] = '3.1.x';
			elseif ($ver >= '522.11')
				$info['version'] = '3.0.x';
			elseif ($ver >= '412')
				$info['version'] = '2.0.x';
			elseif ($ver >= '312')
				$info['version'] = '1.3.x';
			elseif ($ver >= '125')
				$info['version'] = '1.2.x';
			elseif ($ver >= '100')
				$info['version'] = '1.1.x';
			elseif ($ver > '85')
				$info['version'] = '1.0.x';
			elseif ($ver > '0')
				$info['version'] = $ver;
			else
				$info['version'] = '';
		}

		// web downloader? or bot(crawler)?
		if ($info['browser'] == '-1') {
			$info['browser'] = $this->_determineBot($_ua);
		}

		// Mozilla browser check must after bot check. Sooooo many bots use "mozilla/xxxxx"
		// Mozilla can be used as a 'compatible' browsers
		if ( $info['browser'] == "-1" ) {
			if ( strpos( $_ua, 'mozilla/4') !== false || strpos($_ua, 'mozilla/5') !== false ) {
				if ( strpos( $_ua, 'compatible' ) === false ) {
					$info['browser'] = '1';
					ereg( 'mozilla/([[:digit:]\.]+)', $_ua, $_m );
					$info['version'] = $_m[1];
				} elseif ( strpos( $_ua, 'google desktop' ) !== false ) {
					$info['browser'] = '21';
				}
			} elseif ( (strpos($_ua, 'mozilla/5') !== false && strpos($_ua, 'compatible') === false) || strpos($_ua, 'gecko') !== false ) {
				$info['browser'] = '0';
				ereg( 'rv(:| )([[:digit:]\.]+)', $_ua, $_m );
				$info['version'] = $_m[2];
			}
		}
		return $info;
	}

	function _determineBot($_ua) {// lower case user-agent only
		// exact matches
		$i_am_bot = array('mozilla', 'geturl', 'mozilla/4.0 (compatible;)');
		//downloaders
		$download_tools = array('check&get', 'check&amp;get', 'download_express', 'downloader', 'download ', 'getright', 'flashget', 'scraper', 'webcapture', 'wget', 'xget', 'webcopier', 'webzip', 'easydl', 'frontpage', 'recoder', 'fdm ');
		foreach ($download_tools as $dtool) {
			if (strpos($_ua, $dtool) !== false) {
				return '2000';
			}
		}
		//validators
		$validators = array('link valet', 'validity', 'linksmanager', 'mojoo robot', 'validator', 'link system', 'link checker', 'sitebar/', 'checker', 'deadlinkcheck');
		foreach ($validators as $val) {
			if (strpos($_ua, $val) !== false) {
				return '1999';
			}
		}
		//rss readers
		$readers = array('rss-bot', 'rss-spider', 'rss2email', 'reader', 'syndic', 'aggregat', 'subscriber', 'marsedit', 'netvisualize', 'omnipelagos', 'protopage', 'simplepie', 'touchstone', 'feed::find/', 'rss sync');
		foreach ($readers as $read) {
			if (strpos($_ua, $read) !== false) {
				return '1998';
			}
		}
		//general bots
		// cause we filtered known user agent already, just find words widely.
		$bots = array('bot.', 'bot ', 'bot/', 'bot(', 'bot;', 'b-o-t', 'bot@', 'bot)', 'bot-', '-bot', 'robots', 'spider.', 'spider ', 'spider/', 'spider(', 'spider;', 'spider@', 'spider)', 'spider_', ' spider', '-spider', 'spider-', 'spider+', 'get/', 'get(', 'crawl', 'grabber', 'yeti', 'wisenut', 'msnbot', '1noon', 'seeker', 'java ', 'java/', 'fetch', 'collector', 'email ', 'e-mail ', 'machine', 'wisebot', 'capture', 'scrap', 'daum', 'empas', 'phantom', 'harvest', 'yandex', 'rambler', 'aport', 'naverbot', 'nhnbot', 'altavista', 'wanadoo', 'bbc.', 'alltheweb', 'looksmart', 'gigablast', 'teoma', 'clusty', 'hotbot', 'tesco', 'fantomas', 'godzilla', 'greenbrowser', 'surf', 'search', 'engine', 'spider', 'traq', 'track', 'college', 'collage', 'proxy', 'find', 'updater', 'snoop', 'digg', 'hatena', 'libw', 'tool', 'scan', 'monitor', 'activex', 'loader', 'download', 'retrieve', 'ripper', 'snatch', 'control', 'hacker', 'extractor', 'wisponbot');
		foreach ($bots as $bot) {
			if (strpos($_ua, $bot) !== false) {
				return '34';
			}
		}
		//miscellaneous bots
		$bots2 = array('ask ', 'ask.', 'fast ', 'fast-', 'szukaj', 'boitho', 'envolk', 'ingrid', '/dmoz', 'accoona', 'arachmo', 'b-o-t', 'htdig', 'archive', 'larbin', 'linkwalker', 'lwp-trivial', 'mabontland', 'mvaclient', 'nicebot', 'oegp', 'pompos', 'pycurl', 'sbider', 'scrubby', 'discovery', 'silk/', 'snappy', 'sqworm', 'updated', 'voyager', 'vyu2', 'zao', 'missigua', 'pussycat', 'psycheclone', 'shockwave', 'www-form-urlencoded', 'jakarta', 'adwords', 'grub', 'hanzoweb', 'indy library', 'murzillo', 'poe-component', 'webster', 'yoono', 'browsex', 'htmlgobble', 'httpcheck', 'httpconnect', 'httpget', 'imagelock', 'incywincy', 'informant', 'carp', 'blogpulse', 'blogssay', 'edgeio', 'pubsub', 'pulpfiction', 'youreadme', 'pluck', 'justview', 'antenna', 'walker', 'sucker', 'catch', 'webcopy', 'linker', 'worm', 'jeeves', 'javabee', 'abacho', 'agentname', 'become', 'best whois', 'bookdog', 'bravobrian bstop', 'ccubee', 'cjnetworkquality', 'conexcol.com', 'convera', 'cyberspyder link test', 'deepindex', 'depspid', 'directories', 'dlc', 'domain dossier', 'dtaagent', 'earthcom', 'earthcom', 'eventax', 'excite', 'favorg', 'favorites sweeper', 'filangy', 'galaxy', 'gazz', 'gjk_browser_check', 'hotzonu', 'http/', 'iecheck', 'iltrovatore-setaccio', 'internetlinkagent', 'internetseer', 'isilox', 'jrtwine', 'keyword density', 'linkalarm', 'linklint', 'linkman', 'lycoris desktop/lx', 'mackster', 'mail.ru', 'medhunt', 'metaspinner', 'minirank', 'mozdex', 'n-stealth', 'netpromoter', 'netvision', 'ocelli', 'octopus', 'omea pro', 'orbiter', 'pagebull', 'poirot', 'poodle', 'popdex', 'powermarks', 'rawgrunt', 'redcell', 'rlinkcheker', 'robozilla', 'sagoo', 'sensis', 'shopwiki', 'shunix', 'singing fish', 'spinne', 'sproose', 'subst?cia', 'supercleaner', 'syncmgr', 'szukacz', 'tagyu', 'tkensaku', 'twingly recon', 'ucmore', 'updatebrowscap', 'urlbase', 'vagabondo', 'vermut', 'vse link tester', 'w3c-webcon', 'walhello', 'webbug', 'weblide', 'webox', 'webtrends', 'whizbang', 'worqmada', 'wotbox', 'xml sitemaps generator', 'xyleme', 'zatka', 'zibb', 'ogeb', 'www_browser', 'blogdimension', 'gm rss panel', 'planetweb', 'jobo/', 'tycoon', 'html get', 'yodao', 'hmsebot', 'litefinder', 'darxi', 'cr4nk.ws','camelstampede', 'search project', 'rome client', 'webelixir', 'pathtraq/', 'newmoni', 'veoh-', 'colcol.net', 'webrobot', 'tags2dir', 'surveybot', 'doa/', 'atfile.com', 'unknown :',
		/* Bad bots */
		'3d-ftp', 'activerefresh', 'amazon.com', 'amic', 'anonymizer', 'anonymizied', 'anonymous', 'artera', 'asptear', 'autohotkey', 'autokrawl', 'automate5', 'b2w', 'backstreet browser', 'basichttp', 'beamer', 'bitbeamer', 'bits', 'bittorrent', 'blocknote.net', 'blue coat', 'bluecoat', 'brand protect', 'ce-preload', 'cerberian', 'cfnetwork', 'changedetection', 'charlotte', 'cherrypickerelite', 'chilkat', 'cobweb', 'coldfusion', 'copyright/plagiarism', 'copyrightcheck', 'custo', 'datacha0s', 'disco pump', 'dynamic+', 'easyrider', 'ebingbong', 'emeraldshield', 'ezic.com', 'fake ie', 'flatland', 'forschungsportal', 'locator', 'gamespyhttp', 'gnome-vfs', 'got-it', 'gozilla', 'hcat', 'market', 'holmes', 'hoowwwer', 'html2jpg', 'http generic', 'httperf', 'httpsession', 'httpunit', 'hyperestraier', 'eureka', 'ineturl', 'intelix', 'ninja', 'ip*works', 'ipcheck', 'kapere', 'kevin', 'lachesis', 'leechftp', 'lftp', 'linktiger', 'looq', 'lorkyll', 'mailmunky', 'mapoftheinternet', 'metatagsdir', 'foundation', 'mfc_tear_sample', 'microsoft', 'mister pix', 'moozilla', 'ms ipp', 'ms opd', 'myzilla', 'naofavicon4ie', 'net probe', 'net vampire', 'net_vampire', 'netants', 'netcarta_webmapper', 'netmechanic', 'netprospector', 'netpumper', 'netreality', 'nextthing.org', 'nozilla', 'nudelsalat', 'nutch', 'ocn-soc', 'octopodus', 'offline browsers', 'pagedown', 'pageload', 'pajaczek', 'antivirus', 'panscient', 'pavuk', 'peerfactory', 'photostickies', 'pigblock', 'pingdom', 'plinki', 'pogodak!', 'privoxy', 'proxomitron', 'prozilla', 'python', 'relevare', 'repomonkey', 'scoutabout', 'computing', 'shareaza', 'shelob', 'sherlock', 'showxml', 'siteparser', 'sitesnagger', 'sitewinder', 'steeler', 'sunrise', 'superhttp', 'tarantula', 'teleport', 'texis', 'theophrastus', 'thunderstone', 'trend micro', 'tweakmaster', 'twiceler', 'uoftdb experiment', 'url control', 'url2file', 'urlcheck', 'urly warning', 'vegas', 'versatel', 'vobsub', 'vortex', 'magnet', 'webbandit', 'webcheck', 'webclipping', 'webcorp', 'webenhancer', 'webgatherer', 'webinator', 'webminer', 'webmon', 'webpatrol', 'webreaper', 'websauger', 'quester', 'winhttp', 'www-mechanize', 'www4mail', 'wwwster', 'xenu', 'y!oasis', 'yoow!', 'zeus', '(compatible)', '(compatible):', '(compatible; ):', 'compatible; IDZap', 'compatible; ics ', 'zuzara/', 'db browse ', 'blogging to the bank', 'sot 5.1 security kol', 'rulinki.ru', 'megabrowser',
		);

		foreach ($bots2 as $bot2) {
			if (strpos($_ua, $bot2) !== false) {
				return '34';
			}
		}
		return '-1';
	}

	function _determineMobileBrowser($_ua, &$info) {
		$sniffs = array(
			// browsers
			array( 'webpro', '1002', '', '', '74'),
			array( 'netfront', '1003', 'netfront/([[:digit:]\.]+)', 1),
			array( 'xiino', '1004', 'xiino/([[:digit:]\.]+)', 1),
			array( 'jig browser', '1018', '', ''),
			array( 'blazer', '1010', '', '', '74'),
			array( 'openwave mobile browser', '1020', '', ''),//openwave up.browser
			array( 'up.browser', '1020', 'up.browser/([[:digit:]\.]+)', 1),//openwave up.browser
			array( 'up.link/', '1020', 'up.link/([[:digit:]\.]+)', 1),//openwave up.browser
			array( 'mspie ', '1023', '', '', '71'),
			array( 'iemobile', '1023', '', '', '72'),
			array( 'mobileexplorer/', '1023', '', '', '72'),
			array( 'wm5 pie', '1023', '', '', '72'),
			array( 'semc browser', '1024', '', ''),
			array( 'semc-browser', '1024', '', ''),
			array( 'eudoraweb', '1027', '', '', '74'),
			array( 'minimo', '1028', 'minimo/([[:digit:]\.]+)', 1),
			array( 'plucker/', '1029', '', ''),
			array( 'opera mobi', '1033', 'opera/([[:digit:]\.]+)', 1 ),
			array( 'opera mini', '1033', 'opera mini/([[:digit:]\.]+)', 1 ),// added later but checked first
			array( 'ibisbrowser:', '1037', '', '', '71'),
			array( 'avantgo', '1043', '', ''),

			// mobile device manufacturer as browser
			array( 'psp (playstation portable); ', '1001', 'psp (playstation portable); ([[:digit:]\.]+)', 1, '78'),// Sony PSP
			array( 'blackberry/', '1005', 'blackberry/([[:digit:]\.]+)', 1),// pda
			array( 'blackberry', '1005', 'blackberry[^/]*/([[:digit:]\.]+)', 1),// pda
			array( 'orange spv', '1006', '', 1),// orange spv
			array( 'lg-lu', '1007', '',''),// LGT
			array( 'lg-lv', '1007', '',''),// LGT
			array( 'lg-lx', '1007', '',''),// LGT
			array( 'lg-ct', '1007', '',''),// LGT
			array( 'lge-', '1007', '', ''),// LGT
			array( 'lge ', '1007', '', ''),// LGT
			array( 'lg/u', '1007', '', ''),// LGT
			array( 'lg/b', '1007', '', ''),// LGT
			array( 'mot-', '1008', '', ''),//pda // motorola
			array( 'motorola', '1008', '', ''),//pda // motorola
			array( 'nokian-gage', '1009', '', ''),//pda NokiaN-Gage
			array( 'nokia', '1009', '', ''),//pda Nokia
			array( 'samsung-', '1012', '', ''),// samsung
			array( ' sie-', '1011', '', ''),// siemens
			array( ' sec-', '1012', '', ''),// samsung
			array( ' sph-', '1012', '', ''),// samsung
			array( ' sgh-', '1012', '', ''),// samsung
			array( 'sonyericsson', '1013', '', ''),
			array( 'dopod', '1014', '', ''),
			array( 'o2 xda', '1015', '', '', '71'),
			array( 'doris/', '1016', '', '', '77'),
			array( 'doris ', '1016', '', '', '77'),
			array( 'iphone', '1017', '', '', '10'),			
			array( 'ipod', '1017', '', '', '10'),// ipod as iphone
			array( 'kddi-', '1019', '', ''),
			array( 'obigo', '1021', '',''),
			array( 'au-mic/', '1021', '',''),
			array( 'playstation 3', '1022', '', '3', '78'),
			array( 'playstation', '1022', '', '1', '78'),
			array( 'sony ps2', '1022', '', '2', '78'),
			array( 'vodafone', '1025', '', '', '71'),
			array( 'j-phone/', '1025', '', ''),
			array( 'ddipocket', '1026', '', ''),
			array( 'pdxgw/', '1026', '', ''),
			array( 'astel/', '1026', '', ''),
			array( 'hp ipaq', '1030', '', ''),
			array( 'portalmmm/', '1031', '', ''),
			array( 'nintendo wii', '1032', '', ''),
			array( 'nintendo-wii', '1032', '', ''),
			array( 'nitro)', '1032', '', ''),
			array( 'palmsource', '1034', '', ''),
			array( 'epoc', '1035', '', ''),
			array( 'sprint:', '1036', '', '', '71'),
			array( ' im-s', '1038', '', ''),
			array( ' im-u', '1038', '', ''),
			array( 't-mobile', '1039', '', '', '79'),
			array( 'htc-', '1042', '', ''),
			array( 'htc_', '1042', '', ''),
			array( 'htcp', '1042', '', ''),
			array( 'danger hiptop', '1044', '', ''),
			array( 'kindle', '1045', '', ''),// Amazon Kindle

			);

		$this->parse_browser($sniffs, &$info, $_ua);

		if ($info['browser'] != '-1')
			return $info['browser'];

		// borrowed from http://www.andymoore.info/php-to-detect-mobile-phones/
		$_acc = $_SERVER['HTTP_ACCEPT'];
		$misc_mobile = array('1207', '3gso', '4thp', '501i', '502i', '503i', '504i', '505i', '506i', '6310', '6590', '770s', '802s', 'a wa', 'acer', 'acs-', 'airn', 'alav', 'asus', 'attw', 'au-m', 'aur ', 'aus ', 'abac', 'acoo', 'aiko', 'alco', 'alca', 'amoi', 'anex', 'anny', 'anyw', 'aptu', 'arch', 'argo', 'bell', 'bird', 'bw-n', 'bw-u', 'beck', 'benq', 'bilb', 'blac', 'c55/', 'cdm-', 'chtm', 'capi', 'comp', 'cond', 'craw', 'dall', 'dbte', 'dc-s', 'dica', 'ds-d', 'ds12', 'dait', 'devi', 'dmob', 'doco', 'dopo', 'el49', 'erk0', 'esl8', 'ez40', 'ez60', 'ez70', 'ezos', 'ezze', 'elai', 'emul', 'eric', 'ezwa', 'fake', 'fly-', 'fly_', 'g-mo', 'g1 u', 'g560', 'gf-5', 'grun', 'gene', 'go.w', 'good', 'grad', 'hcit', 'hd-m', 'hd-p', 'hd-t', 'hei-', 'hp i', 'hpip', 'hs-c', 'htc ', 'htc-', 'htca', 'htcg', 'htcp', 'htcs', 'htct', 'htc_', 'haie', 'hita', 'huaw', 'hutc', 'i-20', 'i-go', 'i-ma', 'i230', 'iac', 'iac-', 'iac/', 'ig01', 'im1k', 'inno', 'iris', 'jata', 'java', 'kddi', 'kgt', 'kgt/', 'kpt ', 'kwc-', 'klon', 'lexi', 'lg g', 'lg-a', 'lg-b', 'lg-c', 'lg-d', 'lg-f', 'lg-g', 'lg-k', 'lg-l', 'lg-m', 'lg-o', 'lg-p', 'lg-s', 'lg-t', 'lg-u', 'lg-w', 'lg/k', 'lg/l', 'lg/u', 'lg50', 'lg54', 'lge-', 'lge/', 'lynx', 'leno', 'm1-w', 'm3ga', 'm50/', 'maui', 'mc01', 'mc21', 'mcca', 'medi', 'meri', 'mio8', 'mioa', 'mo01', 'mo02', 'mode', 'modo', 'mot ', 'mot-', 'mt50', 'mtp1', 'mtv ', 'mate', 'maxo', 'merc', 'mits', 'mobi', 'motv', 'mozz', 'n100', 'n101', 'n102', 'n202', 'n203', 'n300', 'n302', 'n500', 'n502', 'n505', 'n700', 'n701', 'n710', 'nec-', 'nem-', 'newg', 'neon', 'netf', 'noki', 'nzph', 'o2 x', 'o2-x', 'opwv', 'owg1', 'opti', 'oran', 'p800', 'pand', 'pg-1', 'pg-2', 'pg-3', 'pg-6', 'pg-8', 'pg-c', 'pg13', 'phil', 'pn-2', 'pt-g', 'palm', 'pana', 'pire', 'pock', 'pose', 'psio', 'qa-a', 'qc-2', 'qc-3', 'qc-5', 'qc-7', 'qc07', 'qc12', 'qc21', 'qc32', 'qc60', 'qci-', 'qwap', 'qtek', 'r380', 'r600', 'raks', 'rim9', 'rove', 's55/', 'sage', 'sams', 'sc01', 'sch-', 'scp-', 'sdk/', 'se47', 'sec-', 'sec0', 'sec1', 'semc', 'sgh-', 'shar', 'sie-', 'sk-0', 'sl45', 'slid', 'smb3', 'smt5', 'sp01', 'sph-', 'spv ', 'spv-', 'sy01', 'samm', 'sany', 'sava', 'scoo', 'send', 'siem', 'smar', 'smit', 'soft', 'sony', 't-mo', 't218', 't250', 't600', 't610', 't618', 'tcl-', 'tdg-', 'telm', 'tim-', 'ts70', 'tsm-', 'tsm3', 'tsm5', 'tx-9', 'tagt', 'talk', 'teli', 'topl', 'tosh', 'up.b', 'upg1', 'utst', 'v400', 'v750', 'veri', 'vk-v', 'vk40', 'vk50', 'vk52', 'vk53', 'vm40', 'vx98', 'virg', 'vite', 'voda', 'vulc', 'w3c ', 'w3c-', 'wapj', 'wapp', 'wapu', 'wapm', 'wig ', 'wapi', 'wapr', 'wapv', 'wapy', 'wapa', 'waps', 'wapt', 'winc', 'winw', 'wonu', 'x700', 'xda2', 'xdag', 'yas-', 'your', 'zte-', 'zeto', 'aste', 'audi', 'avan', 'blaz', 'brew', 'brvw', 'bumb', 'ccwa', 'cell', 'cldc', 'cmd-', 'dang', 'eml2', 'fetc', 'hipt', 'http', 'ibro', 'idea', 'ikom', 'ipaq', 'jbro', 'jemu', 'jigs', 'keji', 'kyoc', 'kyok', 'libw', 'm-cr', 'midp', 'mmef', 'moto', 'mwbp', 'mywa', 'newt', 'nok6', 'o2im', 'pant', 'pdxg', 'play', 'pluc', 'port', 'prox', 'rozo', 'sama', 'seri', 'smal', 'symb', 'treo', 'upsi', 'vx52', 'vx53', 'vx60', 'vx61', 'vx70', 'vx80', 'vx81', 'vx83', 'vx85', 'wap-', 'webc', 'whit', 'wmlb', 'xda-');
		switch(true) {
			case ($_acc && (strpos($_acc,'text/vnd.wap.wml') !== false || strpos($_acc,'application/vnd.wap.xhtml+xml') !== false) ):
				$info['browser'] = 1000;
			break;
			case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])||isset($_SERVER['HTTP_13_PROFILE'])||isset($_SERVER['HTTP_56_PROFILE'])):
				$info['browser'] = 1000;
			break;
			case (in_array(substr($_ua, 0, 4), $misc_mobile)):
				$info['browser'] = 1000;
			break;
		}
		return $info['browser'];
	}

	function _determineMacOS($_ua) {
		// Mac OS - Mac computers have different versions
		if ( strpos($_ua, 'intel mac') !== false ) {
			return '22';// Intel Mac
		} elseif ( strpos( $_ua, 'ppc mac os x' ) !== false || ereg('mac os x',$_ua) ) {
			return '10';// Mac OS X
		} elseif ( strpos( $_ua, 'powerpc' ) !== false || stristr( $_ua, 'ppc' ) !== false ) {
			return '11';// Mac PPC
		} elseif ( strpos( $_ua, '680' ) !== false || stristr( $_ua, '68k' ) !== false ) {
			return '9';// Mac 68k
		}
		return '12';// Generic Mac 
	}

	function _determineWinOS($_ua, $_m) {// lower case user-agent only
		$version = trim( $_m[2] );
		if ( strpos($_ua, 'windows moblie') !== false || strpos($_ua, 'windows ce') !== false || strpos($_ua, 'windows phone') !== false ) {
			return $this->_determineMobileOS($_ua);
		} elseif ( strpos($_ua, 'windows nt 5.0') !== false || strpos($_ua,'windows 2000') !== false ) {
			return '0';// Windows 2000
		} elseif ( strpos($_ua, 'windows nt 5.1') !== false || strpos($_ua,'windows xp') !== false ) {
			return '1';// Windows XP
		} elseif ( strpos($_ua, 'windows nt 5.2') !== false || strpos($_ua,'windows 2003') !== false ) {
			if (strpos($_ua, 'win64') !== false)
				return '25';// Windows XP 64bit
			else
				return '2';// Windows 2003
		} elseif ( strpos($_ua, 'windows nt 6.0') !== false || strpos($_ua,'windows vista') !== false ) {
			return '3';// Windows Vista
		} elseif ( strpos($_ua, 'windows nt 6.1') !== false || strpos($_ua,'windows 7') !== false ) {
			return '46';// Windows 7
		} elseif ( strpos($_ua, 'windows nt 4.0') !== false || strpos($_ua,'winnt4.0') !== false ) {
			return '27';// Windows NT 4.0
		} elseif ( strpos( $version, 'nt' ) !== false ) {
			return '4';// Windows NT
		} elseif (strpos($_ua, 'win 9x 4.90') !== false || strpos($_ua, 'windows me') !== false) {
			return '26';// Windows ME
		} elseif (strpos($_ua, 'windows 98') !== false || strpos($_ua, 'win98') !== false ) {
			return '5';// Windows 98
		} elseif ( strpos($version, '95') !== false || strpos($_ua, 'win95') !== false ) {
			return '6';// Windows 95
		} elseif ( strpos($version, '3.1') !== false || strpos($version, '16') !== false || strpos($version, '16bit') !== false ) {
			return '7';// Windows 3.1
		}
		return '8';// No one matched, it's a generic Windows version
	}

	// powered by firestats(http://firestats.cc)
	function _determineUnixOS($_ua) {// lower case user-agent only
		if ( strpos($_ua, 'linux') !== false ) {
			if ( strpos($_ua, 'debian') !== false ) {
				return '29';//Debian GNU/Linux
			} elseif ( strpos($_ua, 'Mandrake') !== false ) {
				return '30';//Mandrake Linux
			} elseif ( strpos($_ua, 'SuSE') !== false ) {
				return '31';//SuSE Linux
			} elseif ( strpos($_ua, 'Novell') !== false ) {
				return '32';//Novell Linux
			} elseif ( strpos($_ua, 'Ubuntu') !== false ) {
				return '33';//Ubuntu Linux
			} elseif ( preg_match('#red ?hat#i', $_ua) ) {
				return '34';//RedHat Linux
			} elseif ( strpos($_ua, 'Gentoo') !== false ) {
				return '35';//Gentoo Linux
			} elseif ( strpos($_ua, 'Fedora') !== false ) {
				return '36';//Fedora Linux
			} elseif ( strpos($_ua, 'MEPIS') !== false ) {
				return '37';//MEPIS Linux
			} elseif ( strpos($_ua, 'Knoppix') !== false ) {
				return '38';//Knoppix Linux
			} elseif ( strpos($_ua, 'Slackware') !== false ) {
				return '39';//Slackware Linux
			} elseif ( strpos($_ua, 'Xandros') !== false ) {
				return '40';//Xandros Linux
			} elseif ( strpos($_ua, 'Kanotix') !== false ) {
				return '41';//Kanotix Linux
			} elseif ( strpos($_ua, 'android') !== false ) {
				return '80';//Google Android
			}
			return '20'; // Linux generic version
		} elseif ( preg_match( '/x11|inux/i', $_ua ) ) {
			return '20';// Linux, generic
		} // Others...
		elseif ( strpos($_ua, 'freebsd') !== false ) {
			return '21';//FreeBSD
		} elseif ( strpos($_ua, 'netbsd') !== false ) {
			return '42';//NetBSD
		} elseif ( strpos($_ua, 'openbsd') !== false ) {
			return '43';//OpenBSD
		} elseif ( preg_match( '/(irix)[\s]*([0-9]*)/i', $_ua ) ) {
			return '15';// Unix Irix (SGI Irix)
		} elseif ( preg_match( '/(sun|i86)[os\s]*([0-9]*)/i', $_ua ) ) {
			return '14';// Sun OS (Solaris)
		} elseif ( preg_match( '/(hp-ux)[\s]*([0-9]*)/i', $_ua ) ) {
			return '16';// HP Unix
		} elseif ( strpos($_ua, 'unix') !== false ) {
			return '44';//UNIX generic version
		} elseif ( preg_match( '/os\/2|ibm-webexplorer/i', $_ua ) ) {
			return '13';// OS2, there is still someone out there?
		} elseif ( preg_match( '/aix([0-9]*)/i', $_ua ) ) {
			return '17';// Aix
		} elseif ( preg_match( '/dec|osfl|alphaserver|ultrix|alphastation/i', $_ua ) ) {
			return '18';// Dec Alpha
		} elseif ( preg_match( '/vax|openvms/i', $_ua ) ) {
			return '19';// Vax, are browsing a blog with a VAX computer?!
		} elseif ( preg_match( '/(free)?(bsd)/i', $_ua ) ) {
			return '21';// Free BSD
		} elseif ( strpos($_ua, 'amigaos') !==false ) {
			return '23';// AmigaOS (maybe mobile)
		} elseif ( strpos($_ua, 'commodore 64') !== false ) {
			return '24';// C-64	(server)
		} elseif ( strpos($_ua, 'risc os') !== false ) {
			return '28';// RISC OS
		} elseif ( strpos($_ua, 'beos') !== false ) {
			return '45';// BeOS
		}
		return '-1';
	}

	// powered by firestats(http://firestats.cc)
	function _determineMobileOS( $_ua ) {
		if (preg_match('#palmos#i', $_ua)) {
			return '74';// Palm OS
		} elseif (preg_match('#windows ce#i', $_ua)) {
			$platform = '71';// Generic Windows CE
			if ( strpos($_ua, 'ppc') !== false )
				$platform = '72';// Microsoft PocketPC
			if ( strpos($ua, 'smartphone') !== false )
				$platform = '73'; // Microsoft Smartphone
			return $platform;
		} elseif (preg_match('#windows mobile#i', $_ua) || preg_match('#windows phone#i', $_ua)) {
			return '79'; // Windows Mobile
		} elseif (preg_match('#windows#i', $_ua) && preg_match('#ppc#i', $_ua)) {
			return '79'; // not CE but windows ppc => Windows Mobile
		} elseif (preg_match('#qtembedded#i', $_ua)) {
			return '75';// Qtopia
		} elseif (preg_match('#zaurus#i', $_ua)) {
			return '76';// Linux Zaurus
		} elseif (preg_match('#symbian#i', $_ua)) {
			return '77';// Symbian OS
		} elseif (preg_match('#playstation#i', $_ua)) {
			return '78';// Linux WAP
		} elseif (preg_match('#linux#i', $_ua) && preg_match('#android#i', $_ua)) {
			return '80';
		}
		return '-1';
	}

	function _determineVisit( $_remote_ip, $_browser, $_version, $_platform, $_table, $time=0 ) {
		global $wpdb, $SlimCfg;
		if (!$time)
			$time = time();
		$query = "SELECT `visit` FROM `".$_table."`
			WHERE `remote_ip`='".$wpdb->escape( $_remote_ip )."' 
				AND `browser`='".$wpdb->escape( $_browser )."' 
				AND `version`='".$wpdb->escape( $_version )."' 
				AND `platform`='".$wpdb->escape( $_platform )."'
				AND `dt` >= '".( $time - 1800 )."'
			ORDER BY `dt` LIMIT 1 ";
		$row = $wpdb->get_row( $query );
		if ($row) {
			return (int)$row->visit;
		}
		$query = "SELECT MAX(`visit`) AS `visit` FROM `".$_table."` ";
		$row = $wpdb->get_row( $query );
		if ($row) {
			return ((int)$row->visit + 1);
		}
		return 1;
	}

	function _checkIgnoreList($ip) {
		global $SlimCfg;
		$isIgnored = false;
		if ( empty($SlimCfg->exclude['ignore_ip']) )
			return $isIgnored;
		$ip_list = explode(';', $SlimCfg->exclude['ignore_ip']);
		foreach ($ip_list as $ignore) {
			$ignore = trim($ignore);
			// check ip list format
			if ( '' == $ignore || !preg_match("#(\d{1,3})(\.\d{1,3})?(\.\d{1,3})?(\.\d{1,3})?([/|-][[:digit:]\.]+)?#", $ignore, $ip4) )
				continue;

			if (6 > count($ip4)) {// ip range like 61.254(61.254.0.0~61.254.255.255) and single ip addr.
				$isIgnored = strpos($ip, $ip4[0]) === 0;
			} else {
				$isIgnored = $this->netMatch($ip, $ip4[0], $ip4[5][0]);
			}
			if ($isIgnored) break;
		}
		return $isIgnored;
	}

	function netMatch($ip, $net, $sep='/') {
		$ip_long = sprintf("%u", ip2long($ip));
		if ($ip_long == 0 || $ip_long == false || $ip_long == -1)// maybe usless but 0
			return false;
		list($base, $mask) = explode($sep, $net);
		$base .= str_repeat(".0", 3 - (substr_count($base, ".")));
		$base_long = ip2long($base);
		if ($base_long == false || $base_long == -1)
			return false;
		if ($sep == '/') {
			// CIDR : http://en.wikipedia.org/wiki/CIDR, http://member.dnsstuff.com/rc/index.php?option=com_content&task=view&id=24&Itemid=5
			// IP range to CIDR : http://ip2cidr.com/
			// CIDR to IP range : http://grox.net/utils/whatmask/
			if (!$mask || $mask > 32) $mask = 32;
			if ($mask < 8) $mask = 8;
			$mask = pow(2,32) - pow(2, (32 - $mask));
			return (($base_long & $mask) == ($ip_long & $mask));
		} else if ($sep == '-') {
			// check IP format(standard with dot)
			if ( !preg_match("#\d{1,3}(\.\d{1,3})?(\.\d{1,3})?(\.\d{1,3})?#", $mask, $block) )
				return $ip == $base;
			if (count($block) == 1) {
				$block = preg_replace('#(\d{1,3}\.\d{1,3}\.\d{1,3}\.)\d{1,3}#', "$1", $base) . $block[0];
			} else {
				$block = $block[0] . str_repeat(".255", 3 - (substr_count($block[0], ".")));
			}
			$block_long = sprintf("%u", ip2long($block));// fix 32bit system
			if ($block_long == 0 || $block_long == false || $block_long == -1)// php 5 or 4
				return false;
/*			if ($base_long > 0 and $block_long < 0) {// on 32bit system
				if ($ip_long > 0) return $ip_long>=$base_long;
				else return $ip_long<=$block_long;
			}*/
			return ($ip_long>=$base_long and $ip_long<=$block_long);
		}
		return $ip == $base;
	}

	// borrowd form firestats(http://firestats.cc)
	function get_remote_addr() {
		// obtain the X-Forwarded-For value.
		$headers = function_exists('getallheaders') ? getallheaders() : null;
		$xf = isset($headers['X-Forwarded-For']) ? $headers['X-Forwarded-For'] : "";
		if (empty($xf)) {
			$xf = isset($GLOBALS['FS_X-Forwarded-For']) ? $GLOBALS['FS_X-Forwarded-For'] : "";
		}
		$xf .= (empty($xf)) ? '' : ',';
		$xf .= $_SERVER["REMOTE_ADDR"];

		$fwd = explode(",",$xf);
		foreach($fwd as $ip) {
			$ip = trim($ip);
			if ($this->is_public_ip($ip)) 
				return $ip;
		}

		// if we got this far and still didn't find a public ip, just use the first ip address in the chain.
		return $fwd[0];
	}

	function is_public_ip($ip) {
		$ip_long = ip2long($ip);
		if ($ip_long == false || $ip_long == -1)
			return false;
		$cidrs = array('10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '169.254.0.0/16',  '127.0.0.0/8');
		foreach ($cidrs as $cidr) {
			if ($this->netMatch($ip, $cidr, '/'))
				return false;
		}
		return true;
		// 10.0.0.0 - 10.255.255.255, 
		// 172.16.0.0 - 172.31.255.255
		// 192.168.0.0 - 192.168.255.255
		// 169.254.0.0 - 169.254.255.255
		// 127.0.0.0 - 127.255.255.255
	}

	function find_matches($list, $text) {
		foreach($list as $word) {
			$word = trim($word);
			if (empty($word)) { continue; }
			$word = preg_quote($word, '#');
			$word = (strpos($word, '\^') === 0) ? '^'.substr($word, 2):$word;
			$word = (strpos($word, '\$') === (strlen($word) -2)) ? substr($word, 0, strlen($word)-2).'$':$word;
			$word = str_replace('\*', '\S*?', $word);
			if (preg_match('#'.$word.'#i', $text))
				return true;
		}
		return false;
	}

	function is_bot($b_id, $ua, $force=array()) {
		global $SlimCfg;
		if (!empty($SlimCfg->exclude['white_ua'])) {
			$w_list = explode("\n", $SlimCfg->exclude['white_ua']);
			if ($this->find_matches($w_list, $ua))
				return false;
		}
		if (true === $force || 'all' === $force)
			$force = array('bots'=>true, 'feeds'=>true, 'validators'=>true, 'tools'=>true);

		$force = wp_parse_args($force, array('bots'=>false, 'feeds'=>false, 'validators'=>false, 'tools'=>false));

		if ($b_id == '34' || $b_id == '2000' || empty($ua))
			return true;
		if ( ($force['bots'] || $SlimCfg->exclude['ig_bots']) && in_array($b_id, $SlimCfg->bot_array['bots']) )
			return true;
		if ( ($force['feeds'] || $SlimCfg->exclude['ig_feeds']) && in_array($b_id, $SlimCfg->bot_array['feeds']) )
			return true;
		if ( ($force['validators'] || $SlimCfg->exclude['ig_validators']) && in_array($b_id, $SlimCfg->bot_array['validators']) )
			return true;
		if ( ($force['tools'] || $SlimCfg->exclude['ig_tools']) && in_array($b_id, $SlimCfg->bot_array['tools']) )
			return true;
		if ( !empty($SlimCfg->exclude['black_ua']) ) {
			$b_list = explode("\n", $SlimCfg->exclude['black_ua']);
			if ($this->find_matches($b_list, $ua))
				return true;
		}
		return false;
	}

	function tostring($text) {
		if (function_exists('mb_convert_encoding'))
			return mb_convert_encoding(chr(hexdec(substr($text[1], 2, 2))).chr(hexdec(substr($text[1], 0, 2))), 'UTF-8', 'UTF-16LE');
		elseif (function_exists('iconv'))
			return iconv('UTF-16LE', 'UTF-8', chr(hexdec(substr($text[1], 2, 2))).chr(hexdec(substr($text[1], 0, 2))));
		return $text;
	}

	function urlutfchr($text) {
		return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', array(&$this, 'tostring'), $text));
	}

	function get_out_now() { exit; }

	function remove_shutdown_hooks($location='', $status='' ) {
		add_action( 'shutdown', array(&$this, 'get_out_now'), -10 );
		return $location;
	}

	function feed_track_footer() {
		if ( is_feed() ) {
			// try to track standard resources
			add_action( 'shutdown', array( &$this, 'slimtrack' ) );
		}
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSTrack();
		}
		return $instance[0];
	}

}
// end of SSTrack

if (!isset($SSTrack))
	$SSTrack =& SSTrack::get_instance();
?>
