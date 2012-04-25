<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

include_once(SLIMSTATPATH . 'lib/compat.php');

class SSFunction {

	function _translateBrowserID( $browser_id = '-1' ) {
		// SMALLINT -32768 to 32767
		$id2browser = array( 
			0=>'Mozilla', 1=>'Netscape', 2=>'Safari', 3=>'iCab', 4=>'Firefox', 5=>'Firebird', 6=>'Phoenix', 7=>'Camino', 8=>'Chimera', 9=>'Internet Explorer', 10=>'MSN Explorer', 11=>'WordPress', 12=>'BlogSearch Engine', 13=>'AllBlog.net RssSync', 14=>'HanRSS', 15=>'Blogging Client', 16=>'W3C HTML Validator', 17=>'ATOM &amp; RSS Validator', 18=>'W3C CSS Validator', 19=>'Python-urllib', 20=>'NewsGator', 21=>'Google Desktop', 22=>'Java', 23=>'AOL', 24=>'AOL Browser', 25=>'K-Meleon', 26=>'Beonex', 27=>'Opera', 28=>'OmniWeb', 29=>'Konqueror', 30=>'Galeon', 	31=>'Epiphany', 32=>'Kazehakase', 33=>'Amaya', 34=>'Crawler', 35=>'Lynx', 36=>'Links', 37=>'ELinks', 38=>'Thunderbird', 39=>'Flock', 40=>'libwww Perl library', 41=>'Apache Bench tool (ab)', 42=>'SeaMonkey', 43=>'Google MediaPartners', 44=>'Google FeedFetcher', 45=>'GoogleBot', 46=>'MsnBot', 47=>'Yahoo Blogs', 48=>'GigaBot', 49=>'ZyBorg', 50=>'Nutch CVS', 51=>'Ichiro', 52=>'Technorati Bot', 53=>'Heritrix', 54=>'FeedBurner', 55=>'Feedpath(JP)', 56=>'Netvibes', 57=>'GreatNews', 58=>'MagpieRSS', 59=>'livedoor FeedFetcher', 60=>'Snoopy', 61=>'Eolin', 62=>'Mac Finder', 63=>'Movabletype', 64=>'Typepad', 65=>'Drupal', 66=>'AnonyMouse', 67=>'Bluefish', 68=>'Amiga-AWeb', 69=>'avant Browser', 70=>'AmigaVoyager', 71=>'Emacs-W3', 72=>'cURL', 73=>'Democracy', 74=>'Dillo', 75=>'DocZilla', 76=>'edbrowse', 77=>'libFetch', 78=>'IceWeasel', 79=>'IBrowse', 80=>'ICE Browser', 81=>'Danger Hiptop', 82=>'KKman', 83=>'Mosaic', 84=>'NetPositive', 85=>'Songbird', 86=>'Sylera', 87=>'Shiira', 88=>'WebTV (MS)', 89=>'W3M', 90=>'HistoryHound', 91=>'BlogKorea', 92=>'Oregano', 93=>'WDG_Validator', 94=>'Docomo', 95=>'NewsFire', 96=>'NewsAlloy', 97=>'Liferea', 98=>'Hatena RSS', 99=>'Feedshow', 100=>'NetNewsWire', 101=>'Acorn Browse', 102=>'Bonecho', 103=>'Lycos Crawlers', 104=>'Multizilla', 105=>'J2ME/MIDP', 106=>'PHP', 107=>'Yahoo! Slurp', 108=>'Google Bots', 109=>'Google Appliance', 110=>'Yahoo Crawlers', 111=>'Inktomi Crawlers', 112=>'Cheshire', 113=>'Crazy Browser', 114=>'Enigma Browser', 115=>'GranParadiso', 116=>'Iceape', 117=>'K-Ninja', 118=>'Maxthon', 119=>'Minefield', 120=>'MyIE2', 121=>'Bing/msnbot', 122=>'Wii Libnup', 123=>'W3C-checklink', 124=>'Xenu Link Sleuth', 125=>'CSE HTML Validator', 126=>'CSSCheck', 127=>'Cynthia', 128=>'HTMLParser', 129=>'P3P Validator', 130=>'Gregarius', 131=>'Bloglines', 132=>'EveryFeed-Spider', 133=>'!Susie', 134=>'Cocoal.icio.us', 135=>'DomainsDB.net MetaCrawler', 136=>'GSiteCrawler', 137=>'FeedDemon', 138=>'Zhuaxia', 139=>'Akregator', 140=>'AppleSyndication', 141=>'Blog Conversation Project', 142=>'BottomFeeder', 143=>'JetBrains Omea Reader', 144=>'ping.blo.gs', 145=>'Raggle', 146=>'RssBandit', 147=>'SharpReader', 148=>'My Yahoo!', 149=>'Rojo', 150=>'Rmail', 151=>'Sage (Firefox)', 152=>'Daum RSS', 153=>'Thunderbird', 154=>'Windows RSS Platform', 155=>'FeedParser', 156=>'LiveJournal', 157=>'Vienna', 158=>'iTunes', 159=>'QuickTime', 160=>'RealPlayer', 161=>'WorldLingo', 162=>'xMind', 163=>'HP Web PrintSmart', 164=>'Plagger', 165=>'Blog Bridge', 166=>'Fastladder', 167=>'NewsLife', 168=>'RSS Owl', 169=>'YeonMo', 170=>'YOZMN (yozmn.com)', 171=>'Feed On Feeds', 172=>'Technorati Feed Bot', 173=>'CazoodleBot', 174=>'Snapbot (snap.com)', 175=>'UCLA C.S.dept Robot', 176=>'HTTPClientBox', 177=>'ONNET API Bot', 178=>'Wing Feed Bot', 179=>'Openmaru Feed Aggregator', 180=>'WebScouter',  181=>'OpenID Servers',  182=>'REBI-Shoveler',  183=>'Mixsh RSSSync',  184=>'FeedWave', 190 =>'Chrome' , 191 => 'Chromium',

			// Mobile Browsers 
			1000=>'Miscellaneous Mobile', 1001=>'Sony PSP', 1002=>'WebPro', 1003=>'NetFront', 1004=>'Xiino', 1005=>'BlackBerry Mobile', 1006=>'Orange SPV Mobile', 1007=>'LG Mobile', 1008=>'Motorola Mobile', 1009=>'Nokia Mobile', 1010=>'Blazer Mobile', 1011=>'Siemens Mobile', 1012=>'SamSung Mobile', 1013=>'Sony/Ericsson', 1014=>'Dopod Mobile', 1015=>'O2 XDA Mobile', 1016=>'Doris Mobile', 1017=>'iPhone', 1018=>'Jig Mobile', 1019=>'KDDI Mobile', 1020=>'OpenWave Up.Browser', 1021=>'Obigo Mobile', 1022=>'Playstation', 1023=>'Pocket IE', 1024=>'SEMC Browser', 1025=>'Vodafone Mobile', 1026=>'AIR-EDGE', 1027=>'EudoraWeb', 1028=>'Minimo Moblie', 1029=>'Plucker Mobile', 1030=>'HP iPAQ', 1031=>'NEC Mobile', 1032=>'Nintendo Wii', 1033=>'Opera Mobile', 1034=>'PalmSource', 1035=>'Psion Mobile', 1036=>'Sprint Mobile', 1037=>'ibisBrowser Mobile', 1038=>'Sky Phone', 1039=>'T-Mobile', 1040=>'Safari Mobile', 1041=>'IE Mobile', 1042=>'HTC Mobile', 1043=>'AvantGo', 1044=>'Danger Hiptop', 1045=>'Amazon Kindle',
			
			// Miscellaneous Browsers
			1998=>'Miscellaneous Readers', 1999=>'Miscellaneous Validators', 2000=>'Miscellaneous Downloaders',
		);

		return isset($id2browser[$browser_id]) ? __($id2browser[$browser_id], SLIMSTAT_DOMAIN) : __('xx', SLIMSTAT_DOMAIN);	
	}

	function _translatePlatformID( $platform_id = '-1' ) {
		// TINYINT -128 to 127
		$id2platform = array( 
			0=>'Windows 2000', 1=>'Windows XP', 2=>'Windows 2003', 3=>'Windows Vista', 3=>'Windows NT', 5=>'Windows 98', 6=>'Windows 95', 7=>'Windows 3.1', 8=>'Windows generic', 9=>'Mac 68k', 10=>'Mac OS X', 11=>'Mac PPC', 12=>'Mac', 13=>'OS/2', 14=>'Sun OS', 15=>'Unix Irix', 16=>'HP Unix', 17=>'Aix', 18=>'Dec Alpha', 19=>'Vax', 20=>'Linux', 21=>'Free BSD', 22=>'Intel Mac', 23=>'AmigaOS', 24=>'C-64', 25=>'Windows XP 64bit', 26=>'Windows ME', 27=>'Windows NT 4.0', 28=>'RISC OS', 29=>'Debian GNU/Linux', 30=>'Mandrake Linux', 31=>'SuSE Linux', 32=>'Novell Linux', 33=>'Ubuntu Linux', 34=>'RedHat Linux', 35=>'Gentoo Linux', 36=>'Fedora Linux', 37=>'MEPIS Linux', 38=>'Knoppix Linux', 39=>'Slackware Linux', 40=>'Xandros Linux', 41=>'Kanotix Linux', 42=>'NetBSD', 43=>'OpenBSD', 44=>'Generic Unix', 45=>'BeOS', 46=>'Windows 7', 

			// Mobile OS
			71=>'Windows CE', 72=>'Microsoft PocketPC', 73=>'Microsoft Smartphone', 74=>'Palm OS', 75=>'Qtopia', 76=>'Linux Zaurus', 77=>'Symbian OS', 78=>'Linux WAP', 79=>'Windows Mobile', 80=>'Android',
		);

		return isset($id2platform[$platform_id]) ? __($id2platform[$platform_id], SLIMSTAT_DOMAIN) : __('xx', SLIMSTAT_DOMAIN);
	}

	function _translateLocaleCode( $locale = '' ) {
		//check locale type
		$locale_list = split('-', $locale);
		if ($locale_list[0] == 'c') {
			return __($locale, SLIMSTAT_DOMAIN);
		} elseif ($locale_list[0] == 'l') {
			$c = count($locale_list);
			if ($c == 2 ) 
				return __($locale, SLIMSTAT_DOMAIN);
			elseif ($c == 3 ) 
				return __('l-'.$locale_list[1], SLIMSTAT_DOMAIN).'/'.__('c-'.$locale_list[2], SLIMSTAT_DOMAIN);
			elseif ($c > 3) 
				return __('l-'.$locale_list[1], SLIMSTAT_DOMAIN);
		}
		return $locale;
	}

	function get_title($id, $small = false) {// get module title form module id
		if (!$small) {
			$title = array( 1=>'Summary',  2=>'Recent domains',  3=>'Recent search',  4=>'New domains',  5=>'Recent resources',  6=>'Hourly hits',  7=>'Daily hits',  8=>'Weekly hits',  9=>'Monthly hits',  10=>'Top resources',  11=>'Top search',  12=>'Top languages',  13=>'Top domains',  14=>'Internally referred',  15=>'Internal search', 16=>'Top visitors',  17=>'Browser versions',  18=>'Platforms',  19=>'Countries',  20=>'Top referers',  91=>'Browsers',  92=>'Recent visitors' );
		} else {
			$title = array( 1=>'Summary',  2=>'Recent domains',  3=>'Recent search',  4=>'New domains',  5=>'Recent resources',  6=>'Hourly',  7=>'Daily',  8=>'Weekly',  9=>'Monthly',  10=>'Top resources',  11=>'Top search',  12=>'Languages',  13=>'Top domains',  14=>'Internally referred',  15=>'Internal search',  16=>'Top visitors',  17=>'Browser versions',  18=>'Platforms',  19=>'Countries',  20=>'Top referers',  91=>'Browsers',  92=>'Recent visitors' );
		}
		if ($id < 100) {
			return __($title[$id], SLIMSTAT_DOMAIN);
		} else {//pin
			$pinid = floor($id/100) - 100;
			$mo = $id - (($pinid+100)*100) - 1;
			$mos = SSFunction::pin_mod_info($pinid);
			return __($mos['modules'][$mo]['title'], SLIMSTAT_DOMAIN);// try to translate
		}
	}

	function id2module($id_or_func) {// get module function name from id
		global $SlimCfg;
		static $names;
		static $pinid2modlue_merged;

		if (!$names) {
			$names = array( 1=>'_moduleSummary', 2=>'_moduleRecentReferers', 3=>'_moduleRecentSearchStrings', 4=>'_moduleNewDomains', 5=>'_moduleRecentResources', 6=>'_moduleLast24Hours', 7=>'_moduleDailyHits', 8=>'_moduleWeeklyHits', 9=>'_moduleMonthlyHits', 10=>'_moduleTopResources', 11=>'_moduleTopSearchStrings', 12=>'_moduleTopLanguages', 13=>'_moduleTopDomains', 14=>'_moduleInternallyReferred', 15=>'_moduleTopInternalSearchStrings', 16=>'_moduleTopRemoteAddresses', 17=>'_moduleTopBrowsers', 18=>'_moduleTopPlatforms', 19=>'_moduleTopCountries', 20=>'_moduleTopReferers', 91=>'_moduleTopBrowsersOnly', 92=>'_moduleRecentRemoteip' );
		}

		if (!$pinid2modlue_merged && is_array($SlimCfg->pinid2modlue) && !empty($SlimCfg->pinid2modlue)) {
			foreach ($SlimCfg->pinid2modlue as $pinid2module) {
				if (empty($pinid2module)) continue;
				unset($pinid2module['name']);
				$names += (array)$pinid2module;
			}
			$pinid2modlue_merged = true;
		}

		// func name from id
		if (is_int($id_or_func))
			return isset($names[$id_or_func]) ? $names[$id_or_func] : false;
		// id from func name
		if ($_id = array_search($id_or_func, $names))// we don't have id 0
			return $_id;

		return false;		
	}

	function fellow_links($id) {
		global $SlimCfg;
		$links = array();
		$preset = array(
			1 => array(1=>array(1,6,7,8,9), 2=>array(2,4,13), 3=>array(3,11,15), 5=>array(5,10,14,20), 92=>array(92,16)),
			2 => array(1=>array(1,6,7,8,9), 3=>array(3,11,15), 10=>array(10,5,14), 16=>array(16,92), 19=>array(19,12), 20=>array(20,2,13,4), 91=>array(91,17,18))
			);
		$preset[3] = $preset[2];// details = feed

		if ( has_filter('slimstat_fellow_links') )
			$preset = apply_filters('slimstat_fellow_links', $preset, $id);
		$panel = $SlimCfg->get['pn'];

		if (isset($preset[$panel])) {
			if (isset($preset[$panel][$id]))
				return $preset[$panel][$id];

			foreach(array_keys($preset[$panel]) as $key)
				if (in_array($id, $preset[$panel][$key]))
					return $preset[$panel][$key];

			return $links;
		}

		return $links;
	}

	function get_nav() {// deprecated
		return '';
	}

	function filter_switch($interval = true) {
		global $SlimCfg;
		// Retrieve data from url
		$_filter = SLIMSTAT_DEFAULT_FILTER;
		if ( $interval && isset($SlimCfg->get['fd']) ) {
			$_filter = " ts.dt >= ".$SlimCfg->get['fd'][0]." AND ts.dt <= ".$SlimCfg->get['fd'][1]." ";
		}
		if ( isset($SlimCfg->get['fi']) ) {
			if ( $_filter == SLIMSTAT_DEFAULT_FILTER ) $_filter = "";
			else $_filter .= " AND";
			$get_fi = $SlimCfg->my_esc($SlimCfg->get['fi']);
			$_filter_str = ( $SlimCfg->get['ft'] == 0 ) ? " = '{$get_fi}'" : " LIKE '%{$get_fi}%'";
			switch ( $SlimCfg->get['ff'] ) {
				case 0:
					$_filter .= " ts.domain $_filter_str";
					break;
				case 1:
					$_filter .= " ts.searchterms $_filter_str";
					break;
				case 2:
					$_filter_str = SSFunction::_resourcefilter2id($_filter_str, $get_fi);
					$_filter .= " ts.resource $_filter_str";
					break;
				case 3: 
					$_filter_str = SSFunction::convert_ip_filter_string($SlimCfg->get['fi'], $SlimCfg->get['ft']);
					$_filter .= " ts.remote_ip $_filter_str";
					break;
				case 4:
					$_filter .= " ts.browser $_filter_str";
					break;
				case 5:
					$_filter .= " ts.platform $_filter_str";
					break;
				case 6:
					$_filter .= " ts.country = '".preg_replace('|^c-|', '', $SlimCfg->my_esc($SlimCfg->get['fi']))."' ";
					break;
				case 7:
					$_filter .= " ts.language = '".preg_replace('|^l-|', '', $SlimCfg->my_esc($SlimCfg->get['fi']))."' ";
					break;
				case 99:// custom column for Pins
					$_filter .= " %%COLUMN%% $_filter_str";
				break;
				default:
					break;
			}
		}
		return $_filter;
	}

	function get_filter_clause($interval=true) {
		static $filter_clause = array();

		$type = $interval ? 'all' : 'noint';

		if (isset($filter_clause[$type]))
			return $filter_clause[$type];

		$filter_clause[$type] = SSFunction::filter_switch($interval);
		return $filter_clause[$type];
	}

	function convert_ip_filter_string($ip, $ft=0) {
		if ($ft == 0) {
			$str = is_long($ip) ? $ip : sprintf('%u', ip2long($ip));
			return "= {$str}";
		}
		if (is_long($ip))
			$ip = long2ip($ip);
		$ip_ = trim($ip, '.');
		$_ip = $ip_;
		$ip_arr = explode('.', $ip_);
		$rest = (4 - count($ip_arr));
		for($i=0; $i<$rest;$i++) {
			$ip_ .= '.0';
			$_ip .= '.255';
		}
		$ip_ = sprintf('%u', ip2long($ip_));
		$_ip = sprintf('%u', ip2long($_ip));
		return ">= {$ip_} AND ts.remote_ip <= {$_ip}";
	}
	
	function module_before($id='', $class='', $style='') {
		if (is_array($class))
			$class = join(' ', $class);
		if ($id != '')
			$id_attr = ' id="mwrap_'.$id.'"';
		return (!defined('SLIMSTAT_MODULE_AJAX') ?"\n<div><div{$id_attr} class=\"$class\"$style>\n":'');
	}

	function module_after() {
		return (!defined('SLIMSTAT_MODULE_AJAX') ? "\n</div></div><!-- .module -->\n" : '');
	}

	function format_var(&$type, $key, $r=array()) {
		global $wpdb, $SlimCfg;
		static $cache_countall = array();

		if (is_array($type)):
			array_walk($type, array('SSFunction', 'format_var'), &$r);

		else:
		if (strpos($type, '.') === false || is_numeric($type) || !preg_match('|^[a-z0-9\._]+$|i', $type))
			return;// check format

		$_tmp = explode('.', $type, 2);
		$var = $_tmp[0];
		$_type = $_tmp[1];

		switch ($_type) {
			case 'percentage':
				$filter_clause = SSFunction::get_filter_clause();
				$cache_key = $SlimCfg->current_table . md5($filter_caluse);

				if (isset($cache_countall[$cache_key])) {
					$countall = $cache_countall[$cache_key];
				} else {
					$countall = $wpdb->get_var( "SELECT COUNT(*) FROM $SlimCfg->current_table ts WHERE $filter_clause", 0 , 0 );
					$cache_countall[$cache_key] = $countall;
				}
				if ( $countall ) {
					$percentage = ($r[$var] / $countall ) * 100;
					if ($percentage > 0.01)
						$type = round( $percentage, 2 );
					else {
						$decimal = $percentage;
						$n = 0;
						while ($decimal < 1) {
							$decimal = $decimal * 10;
							$n++;
							if ($n > 4) break;
						}
						$type = round( $percentage, $n );
					}
				} else
					$type = 0;
			break;
			case 'date':
				$type = $SlimCfg->time_switch($r[$var], 'blog');
				$type = ($type >= $SlimCfg->midnight_print) ? __('Today', SLIMSTAT_DOMAIN).', '.$SlimCfg->date(__("H:i", SLIMSTAT_DOMAIN), $type) : $SlimCfg->date(__("d M, H:i", SLIMSTAT_DOMAIN), $type);
			break;
			case 'short':
				$type = attribute_escape($SlimCfg->trimString($r[$var]));
			break;
			case 'medium':
				$type = attribute_escape($SlimCfg->trimString( $r[$var], 60 ));
			break;
			case 'long':
				$type = attribute_escape($r[$var]);
			break;
			case 'encode':
				$type = urlencode(attribute_escape($r[$var]));
			break;
			case 'encode_prefix':
				$type = ($var == 'resource' && isset($r['resource_url'])) ? $r['resource_url'] : $r[$var];
				if ($type != "" && !preg_match('|^https?://|i', $type))
					$type = 'http://'.$type;
				$type = ($type == "") ? "":' href="'.attribute_escape($type).'"';
			break;
			case 'remote_ip':
				$type = SSFunction::_whoisLink($r[$var]);
			break;
			case 'long_locale':
				$type = SSFunction::_translateLocaleCode($r[$var]);
			break;
			case 'ip2host':
				$type = $SlimCfg->trimString(gethostbyaddr($r[$var]));
			break;
			case 'p_id2string':
				$type = SSFunction::_translatePlatformID($r[$var]);
			break;
			case 'b_id2string':
				$type = SSFunction::_translateBrowserID($r[$var]);
			break;
			case 'resource2title':
				$type = is_null($r['_resource2title']) ? SSFunction::_guessPostTitle( $r[$var] ) : $r['_resource2title'];
			break;
			case 'flag':
				$type = SSFunction::get_flag($r[$var]);
			break;
			case 'basename':
				$type = basename($r[$var]);
			break;
			case 'integer': default:
				$type = $r[$var];
			break;
		}
		endif;
	}

	function &getModule($id_or_func, $sql, $cols, $args, $chart=false) {
		global $SlimCfg;
		if (!is_int($id_or_func)) {
			if (!$id = (int) SSFunction::id2module($id_or_func))
				return SSFunction::module_before('', 'module') . __('Invalid module name', SLIMSTAT_DOMAIN) . SSFunction::module_after();
		} else 
			$id = $id_or_func;

		$params = array('moid' => $id);
		$defaults = array('style'=>array(), 'class'=>'', 'links'=>0, 'navi'=>0);
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		$classes = array('module');
		$_style = '';

		if (!empty($class)) {
			if (!is_array($class))
				$classes[] = $class;
			else
				$classes = array_merge($classes, $class);
		}

		if ( !empty($style) ) {
			if ( !is_array($style) ) {
				$classes[] = $style;// wide or full
			} else {
				$_style .= ' style="';
				foreach ($style as $prop=>$val)
					$_style .= "{$prop}:{$val};";
				$_style .= '"';
			}
		}

		$args['id'] = $id;

		if ($chart && $SlimCfg->is_chart())
			return SSFunction::getChart($sql, $chart, $args);

		$module = SSFunction::module_before($id, $classes, $_style);
		$module .= "\n<div class=\"module-title\">\n";
		$module .= SSFunction::reload_button($params);


		if ($chart && $SlimCfg->get['view_mode'] == 'chart')
			$module_table =& SSFunction::getChart_js($id);
		else
			$module_table =& SSFunction::getDataTable($sql, $cols, $args);

		if ($navi) {
			$module .= SSFunction::prev_button($params);
			$module .= (!$module_table || '' == $module_table) && 'force' !== $navi ? '' : SSFunction::next_button($params);
		}

		if ($links)
			$module_titles = SSFunction::other_links(SSFunction::fellow_links($id), $id);
		else 
			$module_titles = '<h3>'.SSFunction::get_title($id, true).'</h3>';
		$module .= $module_titles;
		$module .= "\n</div><!-- module-title -->\n";

		$module .= "\n<div id=\"twrap_{$id}\" class=\"twrapper\">\n";
		if (false === $module_table)
			$module_table = "\n<div class=\"noresults-msg\">&nbsp;&nbsp;".__('No results found', SLIMSTAT_DOMAIN)."</div>\n";
		$module .= $module_table;
		$module .= "\n</div><!-- twapper -->\n";

		$module .= SSFunction::module_after();
		return $module;
	}

	function &get_module_custom( $id_or_func, &$content, $args=array(), $chart=false ) {
		global $SlimCfg;
		if (!is_int($id_or_func)) {
			if (!$id = (int) SSFunction::id2module($id_or_func))
				return SSFunction::module_before('', 'module') . __('Invalid module name', SLIMSTAT_DOMAIN) . SSFunction::module_after();
		} else 
			$id = $id_or_func;
		$params = array('moid' => $id);

		$defaults = array('style'=>array(), 'class'=>'', 'links'=>0, 'navi'=>0);
		if (is_string($args))
			$args = array('style'=>$args);
		elseif (!is_array($args))
			$args = (array)$args;
		$args = wp_parse_args($args, $defaults);
		extract($args, EXTR_SKIP);

		$classes = array('module');
		$_style = '';

		if (!empty($class)) {
			if (!is_array($class))
				$classes[] = $class;
			else
				$classes = array_merge($classes, $class);
		}

		if ( !empty($style) ) {
			if ( !is_array($style) ) {
				$classes[] = $style;// wide or full
			} else {
				$_style .= ' style="';
				foreach ($style as $prop=>$val)
					$_style .= "{$prop}:{$val};";
				$_style .= '"';
				if (isset($style['height']) && !empty($style['height'])) {
					$_style_t = ' style="height:'.$height_t.'"';
				}
			}
		}

		$args['id'] = $id;

		if ($chart && $SlimCfg->is_chart())
			return SSFunction::getChart_custom($chart, $args);

		$output = '';
		$output .= SSFunction::module_before($id, $classes, $_style);

		if ( !empty($links) ) {
			if (is_array($links))
				$other_links = SSFunction::other_links($links, $id);
			else
				$other_links = SSFunction::other_links(SSFunction::fellow_links($id), $id);
		} else {
			$other_links = '<h3>'.SSFunction::get_title($id, true).'</h3>';
		}

		$output .= "\t\t<div class=\"module-title\">";
		$output .= SSFunction::reload_button($params);
		if ($navi) {
			$output .= SSFunction::prev_button($params);
			$output .= '' == $content && 'force' !== $navi ? '' : SSFunction::next_button($params);
		}
		$output .= $other_links."</div>\n";

		if (!$content || '' == $content)
			$content = "\n<div class=\"noresults-msg\">&nbsp;&nbsp;".__('No results found', SLIMSTAT_DOMAIN)."</div>\n";
		elseif ($chart && $SlimCfg->get['view_mode'] == 'chart')
			$content =& SSFunction::getChart_js($id);

		$output .= "\t\t<div class=\"twrapper\">\n";


		$output .= $content;
		$output .= "</div>";

		$output .= SSFunction::module_after();

		return $output;
	}

	function &table_row(&$r, &$cols, &$qkey, &$rowstyle) {
		global $SlimCfg;
		$row = '';
		SSFunction::setup_row_data(&$r);

		$rowstyle = ($rowstyle == ' class="tbrow"') ? ' class="tbrow-alt"' : ' class="tbrow"';
		$row .= "\n\t<tr{$rowstyle}>\n";

		$col_def = array('class'=>'regular', 'html'=>'', 'formats'=>array());
		foreach ($cols as $col) {
			if ($col === SLIMSTAT_RESOURCE_COL) {
				$row .= "\t\t<td class=\"linkresource\">";
				if ( !empty($r['resource']) ) {
					$r['_resource2title'] = is_null($r['_resource2title']) ? SSFunction::_guessPostTitle($r['resource']) : $r['_resource2title'];
					$row .= '<a target="_blank" href="'.attribute_escape($r['resource_url']).'" title="'.__('Visit this resource', SLIMSTAT_DOMAIN).': '.trim(strip_tags($r['_resource2title'])).'"><img src="'.$SlimCfg->pluginURL.'/css/external.gif" alt="external" /></a>';
				} else $row .= 'x';
				$row .= "</td>\n";
			} else {
				$col = array_merge($col_def, $col);
				extract($col, EXTR_OVERWRITE);

				$row .= "\t\t<td class=\"$class\">";
				$_formats = $formats[$qkey];
				array_walk($_formats, array('SSFunction', 'format_var'), &$r);
				$html = !empty($_formats) ? vsprintf($html, $_formats) : $html;

				if (isset($r['__feed__'])) {
					$regex = '#<a\s+(.+?)href="([^"]*?)(&amp;|&)panel=3&([^"]*?)"\s+([^>]*?)>\s*<img\s+src="([^"]*?)"\s+alt="filter button"\s+class="icons#i';
					$replace = '<a \1href="\2\3panel=2&\4" \5><img src="\6" alt="filter button" class="icons feed';
					$html = preg_replace($regex, $replace, $html);
				}

				$row .= $html;
				$row .= "</td>\n";
			}
		}
		$row .= "\t</tr>\n";
		return $row;
	}

	function append_feed_data(&$results, $query, $_preg) {
		global $wpdb, $SlimCfg;
		$query_feed = preg_replace($_preg, " {$SlimCfg->table_feed} ts ", $query);
		if ( $results_feed = $wpdb->get_results($query_feed, ARRAY_A) ) {
			// mark as feed
			array_walk($results_feed, create_function('&$a,$b', '$a[\'__feed__\'] = 1;'));
			// array with numeric key will not overwrite the original.
			$results = array_merge($results, $results_feed);
			// sort merged results
			if ( preg_match('|\s+ORDER\s+BY\s+([^\s]+?)(\s+[^\s]+?)?\s+LIMIT|is', $query, $m) ) {
				$sortby = str_replace('ts.', '', $m[1]);
				$sortorder = strtolower(trim($m[2])) == 'desc' ? 'desc' : 'asc';
				__masort($results, $sortby, $sortorder);// sort summed values
				array_splice($results, $SlimCfg->option['limitrows']);
			}
		}
	}

	function &getDataTable( $queries, $cols, $args ) {
		global $wpdb, $SlimCfg;

		$countGoodQueries = 0;
		$_table = "\n<table class=\"module_table\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";

		$_table .= "\t<thead><tr>\n\t\t";
		foreach ($cols as $col) {
			if ($col === SLIMSTAT_RESOURCE_COL)
				$col = array('class'=>'linkresource', 'title'=>'&nbsp;');
			$_table .= "<th class=\"".$col['class']."\">".$col['title']."</th>";
		}
		$_table .= "\n\t</tr></thead>\n";
		$_table .= "\t<tbody>\n";

		foreach ( $queries as $qkey => $query ) {
			if (!$results = $wpdb->get_results( $query, ARRAY_A ))
				$results = array();

			// Show all(common, feed) results on start page.
			$_preg = '|\s+'.$SlimCfg->table_stats.'\s+ts\s+|';
			$_show_all = ($SlimCfg->get['pn'] == 1 || $SlimCfg->get['slim_table'] == 'all') && preg_match($_preg, $query);
			if ($_show_all) {// result from both table common, feed.
				SSFunction::append_feed_data(&$results, $query, $_preg);
			}

			if (empty($results)) continue;
			$countGoodQueries++;

			foreach( $results as $r ) {
				$_table .= SSFunction::table_row($r, $cols, $qkey, $rowstyle);
			}
		}
		$_table .= "\t".'</tbody></table>'."\n";
		if ( $countGoodQueries == 0 ) return false;
		return $_table;
	}

	function &getChart_js( $id ) {
		global $SlimCfg;

		$args = array(/*'panel'=>false, */'moid'=>$id, 'action'=>'request_chart', 'view_mode'=>false);
		$data_url = SSFunction::get_url($args, $SlimCfg->pluginURL.'/lib/slimstat-ajax.php?', '&');
//		var_dump($data_url);
		$data_url = urlencode($data_url);

		$_chart = "\n\n\t<div id=\"slimstatchart_".$id."\"></div>\n";

		$_chart .= '<script type="text/javascript">/*<![CDATA[*/
			swfobject.embedSWF(
			"'.$SlimCfg->pluginURL.'/lib/ofc/open-flash-chart-slimstat.swf",
			"slimstatchart_'.$id.'",
			"100%", "100%", "9.0.0", "'.$SlimCfg->pluginURL.'/lib/ofc/expressInstall.swf",
			{"data-file":"'.$data_url.'"},
			{"wmode":"transparent", "allowscriptaccess": "always"}
			);
		/*]]>*/</script>';

		return $_chart;
	}

	function &getChart_custom($chart_conf, $args) {
		if (!$chart_conf || !is_array($chart_conf['data']) || empty($chart_conf['data']))
			return false;

		if (!in_array($chart_conf['type'], array('pie', 'area', 'line', 'bar')))
			return false;

		$chart_conf_def = array('type'=>'bar', 'legend'=>false, 'tip'=>'#label#<br>#val#', 'onclick'=>null, 'data'=>array());
		$chart_conf = wp_parse_args($chart_conf, $chart_conf_def);

		$colours = SSFunction::get_chart_colours($args['id']);
		$chart = SSFunction::create_chart($chart_conf['legend']);

		$el = SSFunction::create_chart_elements(&$chart_conf, &$colours);
		
//		$data = array();
//		SSFunction::build_chart_data_custom(&$data, &$chart_conf);

		$max = $min = 0;
		SSFunction::set_chart_values(&$chart, &$el, $chart_conf['data'], &$chart_conf['type'], &$max, &$min);

		if ($chart_conf['type'] != 'pie') {
			SSFunction::set_chart_axis(&$chart, $max, $min, $chart_conf['data'][0]['text']);
		}

		return $chart->toPrettyString();
	}

	function &getChart( $queries, $chart_conf, $args ) {
		global $wpdb, $SlimCfg;

		$countGoodQueries = 0;
		if (!$chart_conf || !is_array($chart_conf['data']) || empty($chart_conf['data']))
			return false;

		if (!in_array($chart_conf['type'], array('pie', 'area', 'line', 'bar')))
			return false;

		foreach ( $queries as $qkey => $query ):
		if (!$results = $wpdb->get_results( $query, ARRAY_A ))
			$results = array();

		// Show all(common, feed) results on start page.
		$_preg = '|\s+'.$SlimCfg->table_stats.'\s+ts\s+|';
		$_show_all = ($SlimCfg->get['pn'] == 1 || $SlimCfg->get['slim_table'] == 'all') && preg_match($_preg, $query);

		if ($_show_all) {// result from both table common, feed.
			SSFunction::append_feed_data(&$results, $query, $_preg);
		}

		if (empty($results)) continue;
		$countGoodQueries++;

		$chart_conf_def = array('type'=>'bar', 'legend'=>false, 'tip'=>'#label#<br>#val#', 'click'=>null, 'data'=>array());
		$chart_conf = wp_parse_args($chart_conf, $chart_conf_def);
		$colours = SSFunction::get_chart_colours($args['id']);
		$chart = SSFunction::create_chart($chart_conf['legend']);

		$el = SSFunction::create_chart_elements(&$chart_conf, $colours);

		$data = array();
		foreach( $results as $r ) {
			SSFunction::build_chart_data(&$data, $r, $chart_conf, $qkey);
		}

		$max = $min = 0;
		SSFunction::set_chart_values(&$chart, &$el, &$data, &$chart_conf['type'], &$max, &$min);

		if ($chart_conf['type'] !== 'pie') {
			SSFunction::set_chart_axis(&$chart, $max, $min, $chart_conf['data'][0]['text']);
		}
		endforeach;

		if ( $countGoodQueries == 0 ) return SSFunction::getNullChart($chart_conf);

		return $chart->toPrettyString();
	}

	function &getNullChart($chart_conf) {
		$chart = SSFunction::create_chart();
		$el = new pie();
		$el->{'on-show'} = false;
		$el->gradient_fill();
		$el->tooltip('#key#');
		$tmp = new pie_value(100, 'No Results Found');
		$tmp->text = 'No Results Found';
		$el->set_values(array($tmp));
		$chart->add_element( $el );
		return $chart->toPrettyString();
	}

	function get_chart_colours($id=0) {
		$colours = array(
			"#3b270e","#5e0e1c","#940720","#653d09","#89520b","#e17c06","#ca5d2f","#223b07","#529606","#c5c56d","#83176f","#b180ae","#907caf","#7aa84d","#494949","#897869","#8e7653","#a25334","#8eb589","#18338c","#3369c2","#4486b9","#51627c","#6d6d6d","#84a6b2","#c28272"/*,"#9e4747","#d98080","#faa7a2","#6b2e2e","#c2c461","#dcdb81","#d17649","#343b2c","#6c6a6b","#fad0c2","#f2945a","#2e4045","#ababab","#cbe36b","#d98080","#faa7a2","#717699","#6e5980","#d8b9ff","#a868ff","#d199ff","#f368ff","#ff85ef","#846358","#9d5e47","#009090","#780030","#e6c210","#5c4c2e","#c7264b","#8b9e4c","#343d45","#9c3243","#1a1018","#ee0e4e","#396b62","#64a658","#d7de7a","#61b08f","#6c8c66"*/
		);

/*		$id += (int)date('d');

		$c = count($colours);
		if ($id && $id = $id%$c) {
			$colours1 = array_slice($colours, $id);
			$colours2 = array_slice($colours, 0, $id);
			$colours = $colours1 + $colours2;
		}*/
		shuffle($colours);
		return $colours;
	}

	function set_chart_values(&$chart, &$el, &$data, $type, $max=0, $min=0) {
		global $SlimCfg;
		foreach ($data as $k => $d) {
			if (!$el[$k]) continue;
			$values = $type != 'pie' ? $d['values'] : $d;
			if ($type != 'pie' && !empty($values)) {
				$max = max(max($values), (int)$max);
				$min = min(min($values), (int)$min);
			}
		if (!empty($data[0]['onclick'])) {
			foreach ($values as $i => $v) {
				if (!$data[0]['onclick'][$i] || is_object($v))
					continue;
				$tmp = new stdClass;
				$tmp->value = $v;
				$tmp->label = $v;
				$tmp->text = $data[0]['text'][$i];
				$tmp->{'on-click'} = $data[0]['onclick'][$i];
				$tmp->{'on-click-window'} = '_self';
				$tmp->{'on-click-text'} = $tmp->{'on-click'};
				if ($SlimCfg->option['use_ajax'])
					$tmp->{'on-click'} = 'slimstat_chart_onclick';
				$values[$i] = $tmp;
			}
		}
			$el[$k]->set_values( $values );
			$chart->add_element( $el[$k] );
		}
	}

	function set_chart_axis(&$chart, $max, $min, $labels=array()) {
		$y = new y_axis();
		$steps = floor(($max-$min)/10);
		$y->set_range( $min, $max, $steps );
		$y->labels->colour = "#444444";
		$chart->set_y_axis( $y );

		$x = new x_axis();
		$x->set_steps(1);

		$x_labels = new x_axis_labels();
//		$x_labels->offset = false;
		if (count($labels) < 20 )
			$x_labels->rotate = 315;
		else {
			$x_labels->set_vertical();
			$x_labels->set_steps( 2 );
		}
		$x_labels->set_colour( '#444444' );
//		$x_labels->set_size( 16 );
		$x_labels->set_labels( $labels );
//		$x_labels->visible = false;

		$x->set_labels( $x_labels );

		$chart->set_x_axis( $x );
	}

	function create_chart($legend_pos=false) {
		if (!class_exists('open_flash_chart'))
			require_once(SLIMSTATPATH . 'lib/ofc/php-ofc-library/open-flash-chart.php');

		$chart = new open_flash_chart();

		if ($legend_pos) {
			$legend = new stdClass;
			$legend->position = $legend_pos == 'top' ? 'top' : 'right';
			$legend->visible = true;
			if ($legend->position == 'right') {
				$legend->bg_colour = '#f1f1f1';
				$legend->border = true;
				$legend->border_colour = '#aeaeae';
				$legend->shadow = true;
			}
			$chart->legend = $legend;
		}

		$tooltip = new stdClass;
		$tooltip->shadow = true;
		$tooltip->stroke = 2;
		$tooltip->colour = "#aeaeae";
		$tooltip->background = "#777777";
		$tooltip->title = "{font-size: 11px; font-weight:normal; color: #fefefe;}";
		$tooltip->body = "{font-size: 10px; font-weight: normal; color: #87b75c;}";

		$chart->tooltip = $tooltip;
		$chart->set_bg_colour( "#f0f0f0" );
		
		return $chart;
	}

	function create_chart_elements($chart_conf, $colours) {
		$el = array();
		foreach ( $chart_conf['data'] as $k => $data ) {
			switch ($chart_conf['type']):
			case 'pie':
			$el[$k] = new pie();
			$el[$k]->start_angle(35);
			$el[$k]->add_animation( new pie_fade() );
			$el[$k]->add_animation( new pie_bounce(6) );
			$el[$k]->{'on-show'} = false;
			//$el[$k]->label_colour('#432BAF') // <-- uncomment to see all labels set to blue
			$el[$k]->gradient_fill();
			$el[$k]->tooltip( $chart_conf['tip'] );
			$el[$k]->colours( $colours );

			if ($chart_conf['legend'])
				$el[$k]->{"key-on-click"} = 'toggle-visibility';//$chart_conf['key-on-click'];

//			$pie[$k]->set_label_colour('#000000');
//			$pie[$k]->set_no_labels();

			break;
			case 'bar':

			break;
			case 'line': case 'area':
			$d = new stdClass();
			$d->type = 'hollow-dot';
			$d->{'dot-size'} = 4;
			$d->{'halo-size'} = 1;
			$d->colour = $colours[$k];
			$d->tip = $chart_conf['tip'];

			$el[$k] = new $chart_conf['type']();
			$el[$k]->set_default_dot_style($d);
//			$el[$k]->set_values( $data['values'] );
			$el[$k]->set_width( 3 );
			$el[$k]->set_colour( $colours[$k] );
			$el[$k]->{'gradient-fill'} = true;
			$el[$k]->{'fill-alpha'} = "0.7";
			$el[$k]->{"key-on-click"} = 'toggle-visibility';//$chart_conf['key-on-click'];

			$el[$k]->{'on-show'}->type = false;
			$el[$k]->{'on-show'}->cascade = 0;
			$el[$k]->{'on-show'}->delay = 0;

			$el[$k]->text = $data['legend'];
			break;
			default:
				return false;
			break;
			endswitch;
		}
		return $el;
	}

/*	function embed_chart($id, $width="100%", $height="100%") {
		global $SlimCfg;
		$protocol = (isset ($_SERVER['HTTPS']) && strtoupper($_SERVER['HTTPS']) == 'ON') ? 'https' : 'http';

    $out[] = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="' . $protocol . '://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" ';
    $out[] = 'width="' . $width . '" height="' . $height . '" id="slimchart_ie'. $id .'" align="middle">';
    $out[] = '<param name="allowScriptAccess" value="sameDomain" />';
    $out[] = '<param name="movie" value="'. $SlimCfg->pluginURL .'/lib/ofc/open-flash-chart.swf" />';
    $out[] = '<param name="wmode" value="transparent" />';
    $out[] = '<param name="quality" value="high" />';
		$out[] = '<param name="flashvars" value="get-data=ofc_get_data&amp;id='.$id.'" />';
    $out[] = '<embed src="'. $SlimCfg->pluginURL .'/lib/ofc/open-flash-chart.swf" quality="high" width="'. $width .'" height="'. $height .'" name="slimchart_'. $id .'" align="middle" allowScriptAccess="sameDomain" flashvars="get-data=ofc_get_data&amp;id='.$id.'" ';
    $out[] = 'type="application/x-shockwave-flash" pluginspage="' . $protocol . '://www.macromedia.com/go/getflashplayer" id="slimchart_'. $id .'"/>';
    $out[] = '</object>';
		return join("\n", $out);
	}*/

	function build_chart_data(&$data, $r, $chart, $qkey) {
		global $SlimCfg;
		$r['_resource2title'] = null;
		if (isset($r['resource'])) {
			$r['resource_id'] = $r['resource'];
			$res_row = SSFunction::_id2resource($r['resource_id'], 'all');
			$r['resource'] = $res_row->rs_string;
			$r['resource_url'] = $res_row->url;
		}

		foreach ($chart['data'] as $key => $d) {
			$suffix = strpos($d['value'], '.percentage') ? '%' : '';
			array_walk($d, array('SSFunction', 'format_var'), &$r);
			switch ($chart['type']):
			case 'pie':
			$tmp = new pie_value($d['value'], $d['value'].$suffix);
			$tmp->text = isset($d['text-formats']) && is_array($d['text-formats']) ? vsprintf($d['text'], $d['text-formats']) : $d['text'];
			if ($d['onclick']) {
				$tmp->{'on-click'} = isset($d['onclick-formats']) && is_array($d['onclick-formats']) ? vsprintf($d['onclick'], $d['onclick-formats']) : $d['onclick'];
				$tmp->{'on-click-window'} = '_self';
				$tmp->{'on-click-text'} = $tmp->{'on-click'};
				if ($SlimCfg->option['use_ajax'])
					$tmp->{'on-click'} = 'slimstat_chart_onclick';
			}
			$tmp->{'font-size'} = 10;
			$data[$key][] = $tmp;
			break;
			case 'bar':
			break;
			case 'line': case 'area':
				$data[$key][] = (float)$d['value'];
			break;
			endswitch;
		}
	}

	function build_chart_data_custom(&$data, $chart) {
		global $SlimCfg;

		foreach ($chart['data'] as $key => $val) {
			$suffix = strpos($chart['tip'], '%') ? '%' : '';
			foreach ($val['values'] as $i => $v) {
				switch ($chart['type']):
				case 'pie':
				$tmp = new pie_value($v, $v.$suffix);
				$tmp->text = $chart['data'][$key]['text'][$i];
				if (!isset($tmp->text) && $chart['data'][0]['text'][$i])
					$tmp->text = $chart['data'][0]['text'][$i];
				if ($chart['data'][$key]['onclick'][$i]) {
					$tmp->{'on-click'} = $chart['data'][$key]['onclick'][$i];
					$tmp->{'on-click-window'} = '_self';
					$tmp->{'on-click-text'} = $tmp->{'on-click'};
					if ($SlimCfg->option['use_ajax'])
						$tmp->{'on-click'} = 'slimstat_chart_onclick';
				}
				$tmp->{'font-size'} = 10;
				$data[$key][] = $tmp;
				break;
				case 'line': case 'area': case 'bar':
				if ($chart['data'][0]['onclick'][$i]) {
					$tmp = new stdClass;
					$tmp->value = (float)$v;
					$tmp->{'on-click'} = $chart['data'][0]['onclick'][$i];
					$tmp->{'on-click-window'} = '_self';
					$tmp->{'on-click-text'} = $tmp->{'on-click'};
					if ($SlimCfg->option['use_ajax'])
						$tmp->{'on-click'} = 'slimstat_chart_onclick';
				} else {
					$tmp = (float)$v;
				}
				$data[$key][] = $tmp;
				break;
				endswitch;
			}
		}
	}

	function setup_row_data(&$r) {
		if (isset($r['resource'])) {
			$r['_resource2title'] = null;
			$r['resource_id'] = (int)$r['resource'];
			$res_row = SSFunction::_id2resource($r['resource_id'], 'all');
			$r['resource'] = $res_row->rs_string;
			$r['resource_url'] = $res_row->url;
			$r['site_id'] = (int)$res_row->site_id;
		}
	}

	function _id2resource($id, $return='rs_string') {
		global $wpdb, $SlimCfg;
		$query = "SELECT tr.rs_string, tr.site_id FROM {$SlimCfg->table_resource} tr WHERE tr.id='{$id}' LIMIT 1";
		$row = $wpdb->get_row($query);
		if ($row) {
			if ($return == 'all' || $return == 'url') {
				$sites = $SlimCfg->get_sites();
				$wphost = $SlimCfg->get_url_info('home', 'host');
				if ( $row->site_id < '2' || !isset($sites[$row->site_id]) || $wphost == $sites[$row->site_id]['host'] )
					$row->url = $row->rs_string;
				else
					$row->url = 'http://'.$sites[$row->site_id]['host'].$row->rs_string;
			}
			switch($return):
			case 'rs_string':
				return $row->rs_string;
			break;
			case 'url':
				return $row->url;
			break;
			case 'site_id':
				return (int)$row->site_id;
			break;
			case 'all':
				return $row;
			break;
			endswitch;
		}
		return "";
	}

	function _resource2id($resource, $site_id=0) {
		global $wpdb, $SlimCfg;
		$resource = $wpdb->escape($resource);
		$row = $wpdb->get_row("SELECT tr.id FROM {$SlimCfg->table_resource} tr WHERE tr.rs_md5=MD5('{$resource}') LIMIT 1");
		if ($row)
			return $row->id;
		return false;
	}

	function _resourcefilter2id($resourcefilter, $get_fi='') {
		global $wpdb, $SlimCfg;
		if (strpos($resourcefilter, " LIKE '%") === false) {
			$query_where = "tr.rs_md5 = MD5('{$get_fi}')";
		} else {
			$query_where = "tr.rs_string {$resourcefilter}";
		}
		$resources = $wpdb->get_col("SELECT tr.id FROM {$SlimCfg->table_resource} tr WHERE {$query_where} ");
		if ($resources) {
			return 'IN ('.implode(',', $resources).')';
		}
		return 'IN (-2)';
	}

	function _getFilterForm() {
		global $SlimCfg;

		$_form .= "\t".'<form method="get" action="?page=wp-slimstat-ex" id="slimstat_filter">';
		$_form .= '<p class="slimstat_filter_line1">';
		if (!defined('DOING_AJAX'))
			$_form .= "\t\t".'<input type="hidden" name="page" value="wp-slimstat-ex" />'."\n";
		$_form .= "\t\t".'<input type="hidden" name="panel" id="panel" value="'.$SlimCfg->get['pn'].'" />'."\n";
		$_form .= "\t\t".'<input type="submit" class="submit_filter" value="'.__('Apply', SLIMSTAT_DOMAIN).'" />&nbsp;&nbsp;'."\n";

		if ( isset($SlimCfg->get['fd']) ) {
			$rangeA = $SlimCfg->date('n/j/Y', $SlimCfg->time_switch($SlimCfg->get['fd'][0], 'blog'), false);
			$rangeB = $SlimCfg->date('n/j/Y', $SlimCfg->time_switch($SlimCfg->get['fd'][1], 'blog'), false);
			$range_str = $rangeA . ' - ' . $rangeB;
			$range_int = $SlimCfg->get['fd_encode'];
			$interval_reset = '<span class="reset-filter">{<a href="'.SSFunction::get_url(array('fd'=>'')).'" title="'.__('Reset date range', SLIMSTAT_DOMAIN).'" class="ajax-request-link"> '.__('Reset', SLIMSTAT_DOMAIN).' </a>}</span>';
		} else {
			$first_hit = SSFunction::get_firsthit($type);
			$range_str = $SlimCfg->date('n/j/Y', $SlimCfg->time_switch($first_hit, 'blog'), false).' - '.$SlimCfg->date('n/j/Y', $SlimCfg->midnight_print, false);
			$range_int = '';
			$interval_reset = '';
		}

		$_form .= '<input type="hidden" id="fd" name="fd" value="'.$range_int.'" />';
		$_form .= '<input type="text" size="28" id="fd_str" readonly="true" value="'.$range_str.'" />';
		$_form .= $interval_reset;

		$_form .= "\t\t".'<input size="28" type="text" id="fi" name="fi" value="'.($SlimCfg->get['ff'] < 4 ? attribute_escape($SlimCfg->get['fi']) : '').'" /> '."\n";

		$_form .= "\t\t".'&nbsp;<select name="ff" id="ff">'."\n";
		$_form .= "\t\t".'<option value="0"'.($SlimCfg->get['ff']==0 ? ' selected="selected"' : '').'>'.__('Domain', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'<option value="1"'.($SlimCfg->get['ff']==1 ? ' selected="selected"' : '').'>'.__('Search string', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'<option value="2"'.($SlimCfg->get['ff']==2 ? ' selected="selected"' : '').'>'.__('Resource', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'<option value="3"'.($SlimCfg->get['ff']==3 ? ' selected="selected"' : '').'>'.__('Remote IP', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'</select>'."\n";

		$_form .= "\t\t".'&nbsp;<select name="ft" id="ft">'."\n";
		$_form .= "\t\t".'<option value="0"'.($SlimCfg->get['ft']==0 ? ' selected="selected"' : '').'>'.__('Exact', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'<option value="1"'.($SlimCfg->get['ft']==1 ? ' selected="selected"' : '').'>'.__('Substring', SLIMSTAT_DOMAIN).'</option>'."\n";
		$_form .= "\t\t".'</select>'."\n";

		if (!empty($SlimCfg->get['fi']) && $SlimCfg->get['ff'] < 4)
			$_form .= ' <span class="reset-filter">{<a href="'.SSFunction::get_url(array('fi'=>'')).'" title="'.__('Reset filters', SLIMSTAT_DOMAIN).'" class="ajax-request-link"> '.__('Reset', SLIMSTAT_DOMAIN).' </a>}</span>';

		if ($SlimCfg->get['view_mode'] == 'chart')
			$_form .= ' <span class="reset-filter">{<a href="'.SSFunction::get_url(array('view_mode'=>'table')).'" title="'.__('Switch to table view', SLIMSTAT_DOMAIN).'" class="ajax-request-link"> '.__('Table view', SLIMSTAT_DOMAIN).' </a>}</span>';
		else
			$_form .= ' <span class="reset-filter">{<a href="'.SSFunction::get_url(array('view_mode'=>'chart')).'" title="'.__('Switch to chart view', SLIMSTAT_DOMAIN).'" class="ajax-request-link"> '.__('Chart view', SLIMSTAT_DOMAIN).' </a>}</span>';

		$_form .= '<input type="hidden" name="action" value="request_panel" />';
		if ($SlimCfg->get['view_mode'])
			$_form .= '<input type="hidden" name="view_mode" value="'.$SlimCfg->get['view_mode'].'" />';
		$_form .= wp_nonce_field('slimstat-view-stats', '_wpnonce', false, false);

		$_form .= '</p>';

		if ( !empty($SlimCfg->get['fi']) && isset($SlimCfg->get['ff']) && $SlimCfg->get['ff'] > 3 ) {
			switch($SlimCfg->get['ff']) {
				case 4:
					$filter_type = __('Browser', SLIMSTAT_DOMAIN);
					$filter_string = SSFunction::_translateBrowserID($SlimCfg->get['fi']);
				break;
				case 5:
					$filter_type = __('Platform', SLIMSTAT_DOMAIN);
					$filter_string = SSFunction::_translatePlatformID($SlimCfg->get['fi']);
				break;
				case 6:
					$filter_type = __('Country', SLIMSTAT_DOMAIN);
					$filter_string = __($SlimCfg->get['fi'], SLIMSTAT_DOMAIN);
				break;
				case 7:
					$filter_type = __('Language', SLIMSTAT_DOMAIN);
					$filter_string = __($SlimCfg->get['fi'], SLIMSTAT_DOMAIN);
				break;
				default: //for debug
					$filter_type = __('Unkown', SLIMSTAT_DOMAIN);
					$filter_string = $SlimCfg->get['fi'];
				break;
			}

			$_form .= "\n\t".'<p class="slimstat_filter_line2"><span class="filter_string">&nbsp; '.$filter_type.' :: '.$filter_string.' &nbsp;</span>';
			$_form .= ' <span class="reset-filter">{<a href="'.SSFunction::get_url(array('fi'=>'')).'" title="'.__('Reset filters', SLIMSTAT_DOMAIN).'" class="ajax-request-link"> '.__('Reset', SLIMSTAT_DOMAIN).' </a>}</span></p>';
		}

		$_form .= "\t".'</form>'."\n";

		return $_form;
	}

	function get_url($args, $base='', $join='&amp;') {
		global $SlimCfg;
		$default = array( 'panel' => $SlimCfg->get['pn'],
			'moid'=>'',// 'moparent'=>'',
			'slim_offset'=> $SlimCfg->get['slim_offset'],
			'fi' => $SlimCfg->get['fi_encode'],
			'fd' => $SlimCfg->get['fd_encode'],
			'slim_table' => $SlimCfg->get['slim_table'],
			'action'=>'request_panel',
			'view_mode' => $SlimCfg->get['view_mode']
		);
		$args = wp_parse_args($args, $default);
		// remove pre-added query key form fi_encode, fd_encode
		$args['fi'] = preg_replace('/^&amp;fi=/', '', $args['fi']);
		$args['fd'] = preg_replace('/^&amp;fd=/', '', $args['fd']);

		if ($SlimCfg->get['view_mode'] == 'chart' && $join != '&amp;')
			$args['fi'] = preg_replace('/&amp;(ff|ft)=/', $join.'\1=', $args['fi']);

		if ($args['moid'] && !is_numeric($args['moid']))
			$args['moid'] = SSFunction::id2module($args['moid']);

		$args = array_filter($args);// remove empty args

		if (!isset($args['_wpnonce']))
			$args['_wpnonce'] = wp_create_nonce('slimstat-view-stats');

		if ('' == $base)
			$base = function_exists('admin_url') ? admin_url('?page=wp-slimstat-ex'.$join) : get_option('siteurl') . '/wp-admin/?page=wp-slimstat-ex'.$join;

		$query = _http_build_query($args, null, $join, '', false);

		return $base.$query;
	}

	function blank_image($alt='', $class='blank-img') {
		global $SlimCfg;
		return '<img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="'.$alt.'" class="'.$class.'" />';
	}

	function reload_button($args) {
		global $SlimCfg;
		$args['slim_offset'] = '';// reset
		$args['action'] = 'request_module';
		$url = SSFunction::get_url($args);
		return '<a title="'.__('Reload this module', SLIMSTAT_DOMAIN).'" class="reload slim_button ajax-request-link" href="'.$url.'">'.SSFunction::blank_image('reload').'</a>';
	}

	function next_button($args) {
		global $SlimCfg;
		$args['slim_offset'] = $SlimCfg->get['slim_offset'] + 1;// increase offset
		$args['action'] = 'request_module';
		return SSFunction::prev_next_button('next', $args);
	}

	function prev_button($args) {
		global $SlimCfg;
		if ( !$SlimCfg->get['slim_offset'] )
			return '';
		if ( $SlimCfg->get['slim_offset'] >= 1 )
		 $args['slim_offset'] = $SlimCfg->get['slim_offset'] - 1;// decrease offset
		$args['action'] = 'request_module';
		return SSFunction::prev_next_button('prev', $args);
	}

	function prev_next_button($navi='prev', $args) {
		global $SlimCfg;
		$title = 'title="'.sprintf(__('Show %s results', SLIMSTAT_DOMAIN), 
			$navi == 'prev' ? __('previous',SLIMSTAT_DOMAIN) : __('next',SLIMSTAT_DOMAIN)).'" ';
		$url = SSFunction::get_url($args);
		return '<a '.$title.'class="'.$navi.'_button slim_button ajax-request-link" href="'.$url.'">'.SSFunction::blank_image($navi).'</a>';
	}

	function get_flag($country='') {
		global $SlimCfg;
		//if is remote ip address
		if (strpos($country, '.') !== false) 
			$country = 'c-'.strtolower(SSTrack::_determineCountry($country));
		if (empty($country) || $country == 'c-') 
			$country = 'c-unknown';
		$overlap = array('c-fx'=>'c-fr', 'c-gf'=>'c-fr', 'c-re'=>'c-fr', 'c-mf'=>'c-fr', 'c-hm'=>'c-au', 'c-sj'=>'c-no', 'c-o1'=>'c-unknown');
		if ( isset($overlap[$country]) )
			$country = $overlap[$country];
		return '<img src="'.$SlimCfg->pluginURL.'/css/flags/'.$country.'.png" alt="'.$country.'" class="icons" />';
	}

	// Powered by http://ip-lookup.net/ and http://dnsstuff.com
	function _whoisLink($ip) {
		global $SlimCfg;
		$output = '';
		if ($SlimCfg->option['whois']) {
			$link = $SlimCfg->option['whois_db'] == 'iplookup' ? 'http://ip-lookup.net/?'.$ip : 'http://private.dnsstuff.com/tools/ipall.ch?ip='.$ip.'#map';
			$w_h = $SlimCfg->option['whois_db'] == 'iplookup' ? 'width=550,height=600,scrollbars=yes' : 'width=550,height=800,scrollbars=yes';
			$output .= '<a href="'.$link.'" title="Who is?" onclick="window.open(this.href, \'whois\', \''.$w_h.'\'); return false;">';
		}
		$output .= ($SlimCfg->option['iptohost'])?$SlimCfg->trimString(gethostbyaddr($ip)):$ip;
		$output .= ($SlimCfg->option['whois'])?'</a>':'';
		return $output;
	}

	function module_link($moid, $string='', $args=array()) {
		global $SlimCfg;
		$class = ('' == $string) ? "-title" : "";
		if (!is_array($args))
			$args = array();
		$args['moid'] = $moid;
		$args['action'] = 'request_module';
		$url = SSFunction::get_url($args);
		$string = ('' == $string) ? SSFunction::get_title($moid) : $string;
		$result = '<a class="ajax-request-link mod-link'.$class.'" href="'.$url.'">'.$string.'</a>';
		return $result;
	}

	function other_links($links, $current) {
		global $SlimCfg;
		$result = '';
		if (!is_array($links) || empty($links))
			return $result;
		$result .= '<ul class="module-tabs">';
		$i=1;
		$c = count($links);
		if ( false !== $current_idx = array_search($current, $links) ) {
			unset($links[$current_idx]);
			array_unshift($links, $current);
		}
		foreach($links as $link) {
//			if ($link == 6 && $SlimCfg->get['fd'] && ($SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0]) > 86400) {
//				/*$i++;*/$c--; continue;
//			}

			$classes = array('module-tab', 'module-tab-'.$link);
			$class = '';
			if ($i == 1) $classes[] = 'first-item';
			elseif ($i == $c) $classes[] = 'last-item';
			if ($link === $current) $classes[] = 'current-item';
			if (!empty($classes))
				$class = ' class="'.join(' ', $classes).'"';

			$result .= '<li'.$class.'>'.SSFunction::module_link($link, SSFunction::get_title($link, true), array('slim_offset'=>''));
			$result .= $i == 1 ? '<ul>' : '</li>';
			$result .= $i == $c ? '</ul></li>' : '';
			$i++;
		}
		$result .= '</ul>';
		return $result;
	}

	function pin_mod_info($id) {
		global $wpdb, $SlimCfg;
		$row = $wpdb->get_row("SELECT modules, name, type, active FROM $SlimCfg->table_pins WHERE id = '$id' LIMIT 1", ARRAY_A);
		if ($row) {
			$row['modules'] = unserialize($row['modules']);
			return $row;
		}
		return false;
	}
	//end pin_mod_info

	function filterBtn($args=array(), $panel='') {
		global $SlimCfg;
		$result = "";

		if ($panel == '')
			$panel = $SlimCfg->get['pn'];
		if ($panel == 1)
			$panel = 3;// show filtered results of common table by default.

		if ($panel == 2) $class = ' feed';
		else if ($panel > 100) $class = ' self';
		else $class = '';

		$args['panel'] = $panel;

		$panel_name = SSFunction::get_panel_name($panel);
		$href = SSFunction::get_url($args);
		$result .= '<a href="'.$href.'" title="'.sprintf(__('Filter this on \'%s\' panel', SLIMSTAT_DOMAIN), $panel_name).'" class="filter-link ajax-request-link">';
		$result .= SSFunction::blank_image('filter button', 'icons'.$class).'</a>';
		return $result;
	}

	function get_panel_name($panel=1) {
		if (!$panel)
			return __('unidentified', SLIMSTAT_DOMAIN);
		if ($panel > 100)
			return __('current', SLIMSTAT_DOMAIN);
		if ($panel == 1 || $panel == 3)
			return __('Details', SLIMSTAT_DOMAIN);
		if ($panel == 2)
			return __('Feeds', SLIMSTAT_DOMAIN);
	}

	function chart_onclick_url($args=array(), $panel='') {
		global $SlimCfg;
		if ($panel == '')
			$panel = $SlimCfg->get['pn'];
		if ($panel == 1)
			$panel = 3;// show filtered results of common table by default.
		$args['panel'] = $panel;
		$url = SSFunction::get_url($args, '', '&');
//		if (!$SlimCfg->option['use_ajax'])
			return $url;
		return 'slimstat_chart_onclick';
	}

	function get_filterBtns($args, $custom_module=false) {
		global $SlimCfg;
		$btn = '';
		$panel = $SlimCfg->get['pn'];
		$args['slim_offset'] = '';// reset offset var
		if ($panel == 1) {
			if ($custom_module)
				return SSFunction::filterBtn($args, 3)." ".SSFunction::filterBtn($args, 2);
			else 
				return SSFunction::filterBtn($args, 3);
		}
		$slim_table = $SlimCfg->get['slim_table'] ? $SlimCfg->get['slim_table'] : ($panel > 100 ? 'common' : null);
		if ($slim_table && ($slim_table == 'common' || $slim_table == 'feed')) {
			$btn .= SSFunction::filterBtn($args, ($slim_table == 'feed' ? 2 : 3));
		}
		$btn .= SSFunction::filterBtn($args);
		return $btn;
	}

	function get_hvu( $_table, $interval="", $filters = "") {
		global $wpdb;
		if (is_array($_table)) {
			$hvu1 = SSFunction::get_hvu($_table[0], $interval, $filters);
			$hvu2 = SSFunction::get_hvu($_table[1], $interval, $filters);
			$hvu = array();
			foreach($hvu1 as $key=>$val)
				$hvu[$key] = (int)$hvu1[$key] + (int)$hvu2[$key];
			return $hvu;
		}
		$query = "SELECT COUNT(ts.id) AS hits, COUNT(DISTINCT ts.visit) AS visits, COUNT(DISTINCT ts.remote_ip) AS uniques";
		$query .= " FROM $_table ts WHERE ";
		$query .= (!empty($interval)) ? $interval : SLIMSTAT_DEFAULT_FILTER;
		$query .= (!empty($filters) && $filters != SLIMSTAT_DEFAULT_FILTER)?" AND ".$filters : "";
		if ( $result = $wpdb->get_row( $query, ARRAY_A ) ) {
			return $result;
		}
		return array( "hits" => 0, "visits" => 0, "uniques" => 0 );
	}
	//end get_hvu

	function deleted_hvu($type) {
		global $wpdb, $SlimCfg;
		$query = "SELECT hits, visits, uniques FROM $SlimCfg->table_dt ";
		switch ($type) {
			case 'common':
				$query .= " WHERE type = 11";
			break;
			case 'feed':
				$query .= " WHERE type = 12";
			break;
			case 'all': default:
				$query .= " WHERE type = 13";
			break;
		}
		$hits = 0;
		$visits = 0;
		$uniques = 0;
		if ($rs = $wpdb->get_results($query)) {
			for ($i =0; $i < count($rs); $i++) {
				$hits += (int)$rs[$i]->hits;
				$visits += (int)$rs[$i]->visits;
				$uniques += (int)$rs[$i]->uniques;
			}
		}
		return array( "hits" => $hits, "visits" => $visits, "uniques" => $uniques );
	}
	//end deleted_hvu

	function ins_dt( $_dt_start, $_dt_end = 0, $type = 0, $filters='' ) {
		global $SlimCfg;
		switch ($type) {
			case 2: case 12:
				$_table = $SlimCfg->table_feed;
			break;
			case 1: case 11: 
				$_table = $SlimCfg->table_stats;
			break;
			case 3: case 13:
				$_table = array($SlimCfg->table_stats,$SlimCfg->table_feed);
			break;
			default: break;
		}
		if ( $_dt_end == 0 || $_dt_end >= time()) {
			$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start , $filters );
			return $hvu;
		} else if ( !empty($filters) && $filters != SLIMSTAT_DEFAULT_FILTER ) {
			$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start ." AND ts.dt<=".(int)$_dt_end , $filters );
			return $hvu;
		} else {
			global $wpdb;
			$query = "SELECT hits, visits, uniques ";
			$query .= " FROM $SlimCfg->table_dt ";
			$query .= " WHERE dt_start=".(int)$_dt_start ." AND dt_end=".(int)$_dt_end ." ";
			$query .= " AND type = '$type' LIMIT 1";
			if ( $hvu = $wpdb->get_row( $query, ARRAY_A ) ) {
					return $hvu;
			} else {
				if (is_array($_table)) {
					$hvu1 = SSFunction::ins_dt($_dt_start, $_dt_end, ($type-2), $filters);
					$hvu2 = SSFunction::ins_dt($_dt_start, $_dt_end, ($type-1), $filters);
					$hvu = array();
					foreach($hvu1 as $key=>$val)
						$hvu[$key] = (int)$hvu1[$key] + (int)$hvu2[$key];
				} else {
					$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start ." AND ts.dt<=".(int)$_dt_end  );
				}
				$query = "INSERT INTO $SlimCfg->table_dt ";
				$query .= " ( dt_start, dt_end, hits, visits, uniques, type ) VALUES ( ";
				$query .= (int)$_dt_start .", ".(int)$_dt_end .", ";
				$query .= "".(int)$hvu['hits'].", ".(int)$hvu['visits'].", ".(int)$hvu['uniques'].", ".$type." )";
				$wpdb->query( $query );
				return $hvu;
			}
		}
	}
	//end ins_dt

	function get_firsthit($type = 'common') {
		global $wpdb, $SlimCfg;
		switch ($type) {
			case 'common':
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_stats} ts ";
				$first_hit = (int)$wpdb->get_var( $query, 0, 0 );
			break;
			case 'feed':
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_feed} ts ";
				$first_hit = (int)$wpdb->get_var( $query, 0, 0 );
			break;
			case 'all': default:
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_stats} ts ";
				$time = (int)$wpdb->get_var($query);
				$first_hit_n = ($time) ? $time : time();
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_feed} ts ";
				$time = (int)$wpdb->get_var($query);
				$first_hit_f = ($time) ? $time : time();
				$first_hit = min($first_hit_n, $first_hit_f);
			break;
		}
		return ( $first_hit ? $first_hit : time() );
	}
	//end get_firsthit

	function get_real_firsthit($type) {
		global $wpdb, $SlimCfg;
		$query = "SELECT MIN(dt_start) AS dt_start FROM {$SlimCfg->table_dt}";
		switch ($type) {
			case 'common':
				$query .= " WHERE type = 11";
			break;
			case 'feed':
				$query .= " WHERE type = 12";
			break;
			case 'all': default:
				$query .= " WHERE type > 10";
			break;
		}
		$query .= " LIMIT 1";
		if ($row = $wpdb->get_row($query)) {
			return (int)$row->dt_start;
		}
		return false;
	}
	//end get_real_firsthit

	function calc_hvu($dt_start, $dt_end, $type, $filters='') {
		$numType = array('common'=>1, 'feed'=>2, 'all'=>3);
		if ( !isset($numType[$type]) )
			return array("hits"=>0, "visits"=>0, "uniques"=>0);
		return SSFunction::ins_dt( $dt_start, $dt_end, $numType[$type], $filters );
	}
	//end calc_hvu

	function get_bot_array($force = array()) {
		global $SlimCfg;
		if ( $force === true || $force == 'all' )
			$force = array('bots'=>1, 'feeds'=>1, 'validators'=>1, 'tools'=>1);
		$bots = array('34,2000');
		if ($force['bots'] || $SlimCfg->exclude['ig_bots'])
			$bots = array_merge($bots, $SlimCfg->bot_array['bots']);
		if ($force['feeds'] || $SlimCfg->exclude['ig_feeds'])
			$bots = array_merge($bots, $SlimCfg->bot_array['feeds']);
		if ($force['validators'] || $SlimCfg->exclude['ig_validators'])
			$bots = array_merge($bots, $SlimCfg->bot_array['validators']);
		if ($force['tools'] || $SlimCfg->exclude['ig_tools'])
			$bots = array_merge($bots, $SlimCfg->bot_array['tools']);
		return $bots;
	}

	function is_download($qv= array(), $res = '', $rewrite=false) {
		$is_dl = isset($qv['dl']) && '' != trim($qv['dl']) && !preg_match('|^https?://|i', trim($qv['dl']));// supports download-manager by default.
		if ($is_dl)
			return trim($qv['dl']);
		if ( !has_filter('slimstat_is_download') )
			return false;
		$is_dl = apply_filters('slimstat_is_download', $qv, $res, $rewrite);
		if ( !$is_dl || ($is_dl && !is_string($is_dl)) )
			return false;
		return $is_dl;
	}

	function my_domains($context='db') {
		global $SlimCfg;
		$sites = $SlimCfg->get_sites();
		$domains = array($_SERVER['HTTP_HOST']);
		foreach ($sites as $site)
			if (!empty($site['host']) && !in_array($site['host'], $domains))
				$domains[] = $site['host'];
		$domains = array_unique(array_filter($domains));
		$ret = false;
		switch ($context) {
			case 'db': default:
				return "'" . join("','", $domains) . "'";
			break;
			case 'array':
				return $domains;
			break;				
		}
		return false;
	}

	// powered by parse_request in WP class (WP 2.6)
	function _guessPostTitle($resource='', $track=false, $dev=false) {
		global $SlimCfg;

		$returnarr = $track || $dev;

		if (!$returnarr && !$SlimCfg->option['guesstitle'])
			return wp_specialchars($SlimCfg->trimString($resource, 68), true);

		$before = '<span class="resource-type">';
		$after = '</span>';

		if ( !$returnarr && ($_pre = SSFunction::_getSavedPostTitle($resource)) ) {
			if ($_pre->rs_condition == '') { 
				$before = $after = '';
			} else {
				if (strpos($_pre->rs_condition, '][') !== false) {
					$_condition = explode('][', $_pre->rs_condition);
					$_pre->rs_condition = __($_condition[0].']', SLIMSTAT_DOMAIN).__('['.$_condition[1], SLIMSTAT_DOMAIN);
				} else {
					$_pre->rs_condition = __($_pre->rs_condition, SLIMSTAT_DOMAIN);
				}
			}
			if ('' == $_pre->rs_title) 
				$_pre->rs_title = $resource;
			return $before.$_pre->rs_condition.$after.' '.wp_specialchars($SlimCfg->trimString($_pre->rs_title, 68), true);
		}

		$qv = array();
		$query_vars = array();
		$taxonomy_query_vars = array();

		$req_uri_array = explode('?', $resource);
		$req_uri = $req_uri_array[0];
		$home_path = $SlimCfg->_getWebPath();
		if ( !empty($req_uri_array[1]) )
			parse_str($req_uri_array[1], $qv);
		$req_uri = trim($req_uri, '/');
		$req_uri = preg_replace("|^$home_path|", '', $req_uri);
		$req_uri = trim($req_uri, '/');

		$fullurl = strtolower('http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($resource, '/'));
		// if external tracking or not wp resource
		if ( strpos($fullurl, get_option('home')) === false && strpos($fullurl, get_option('siteurl')) === false ) {
			if ($returnarr)
				return array('job'=>'[unusual]');
			SSFunction::_insertPostTitle('', '[unusual]', $resource);
			return $before.__('[unusual]', SLIMSTAT_DOMAIN).$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		}

		global $wp, $wp_rewrite;
		$public_query_vars = $wp->public_query_vars;
		$private_query_vars = $wp->private_query_vars;

		if ( empty($qv) && ($req_uri == '' || $req_uri == $wp_rewrite->index) ) {
			if ($returnarr)
				return array('type'=>'[home]');
			SSFunction::_insertPostTitle('', '[home]', $resource);
			return $before.__('[home]', SLIMSTAT_DOMAIN).$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		} elseif ( false !== $filename = SSFunction::is_download($qv, $req_uri, $track) ) {// pre-check by query string
			if ($returnarr)
				return array('title'=>$filename, 'type'=>'[download]');
			SSFunction::_insertPostTitle($qv['dl'], '[download]', $resource);
			return $before.__('[download]', SLIMSTAT_DOMAIN).$after.' '.$qv['dl'];
		} elseif ( strpos($resource, '/wp-comments-post.php') !== false ) {
			if ($returnarr)
				return array('job'=>'[add comment]');
			SSFunction::_insertPostTitle('', '[add comment]', $resource);
			return $before.__('[add comment]', SLIMSTAT_DOMAIN).$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		} elseif ( strpos($resource, '/wp-cron.php') !== false ) {
			if ($returnarr)
				return array('type'=>'[schedule]');
			SSFunction::_insertPostTitle('', '[schedule]', $resource);
			return $before.__('[schedule]', SLIMSTAT_DOMAIN).$after.' '.$req_uri; // ignore check query
		}

		// Fetch the rewrite rules.
		$rewrite = $wp_rewrite->wp_rewrite_rules();
		$did_permalink = false;

		if ( empty($rewrite)) {
			// if is not wp resource
			if ( $req_uri != $wp_rewrite->index && $req_uri != '' ) {
				if ($returnarr)
					return array('job'=>'[unusual]');
				SSFunction::_insertPostTitle('', '[unusual]', $resource);
				return $before.__('[unusual]', SLIMSTAT_DOMAIN).$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
			}
		} else {
			$did_permalink = true;
			// If the request uri is the index, blank it out so that we don't try to match it against a rule.
			if ( $req_uri == $wp_rewrite->index )
				$req_uri = '';
			$request = $req_uri;

			// Look for matches.
			$request_match = $request;
			foreach ($rewrite as $match => $query) {
				// If the requesting file is the anchor of the match, prepend it
				// to the path info.
				if ((! empty($req_uri)) && (strpos($match, $req_uri) === 0) && ($req_uri != $request)) {
					$request_match = $req_uri . '/' . $request;
				}
				if (preg_match("!^$match!", $request_match, $matches) ||
					preg_match("!^$match!", urldecode($request_match), $matches)) {
					// Trim the query of everything up to the '?'.
					$query = preg_replace("!^.+\?!", '', $query);
					// Substitute the substring matches into the query.
					eval("\$query = \"$query\";");
					// Parse the query.
					parse_str($query, $perma_query_vars);
					break;
				}
			}
			if ( isset($perma_query_vars) && strpos($request, 'wp-admin/') !== false )
				unset($perma_query_vars);
		}

		if (has_filter('query_vars'))
			$public_query_vars = apply_filters('query_vars', $public_query_vars);

		foreach ( $GLOBALS['wp_taxonomies'] as $taxonomy => $t )
			if ( isset($t->query_var) )
				$taxonomy_query_vars[$t->query_var] = $taxonomy;

		for ($i=0; $i<count($public_query_vars); $i += 1) {
			$wpvar = $public_query_vars[$i];
			if (!empty($qv[$wpvar]))
				$query_vars[$wpvar] = $qv[$wpvar];
			elseif (!empty($perma_query_vars[$wpvar]))
				$query_vars[$wpvar] = $perma_query_vars[$wpvar];

			if ( !empty( $query_vars[$wpvar] ) ) {
				$query_vars[$wpvar] = (string) $query_vars[$wpvar];
				if ( in_array( $wpvar, $taxonomy_query_vars ) ) {
					$query_vars['taxonomy'] = $taxonomy_query_vars[$wpvar];
					$query_vars['term'] = $query_vars[$wpvar];
				}
			}
		}

		foreach ($private_query_vars as $var) {
			if (isset($GLOBALS[$var]) && '' != $GLOBALS[$var])
				$query_vars[$var] = $GLOBALS[$var];
		}

		// if !is_home() && there is no public query vars
		if ( empty($query_vars) ) {
			if ($returnarr)
				return array('job'=>'[unusual]');
			SSFunction::_insertPostTitle('', '[unusual]', $resource);
			return $before.__('[unusual]', SLIMSTAT_DOMAIN).$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		}

		return SSFunction::_getPostTitle(&$query_vars, $before, $after, $resource, $track, $did_permalink, $dev);
	}

	function _getSavedPostTitle($resource) {
		global $wpdb, $SlimCfg;
		if (is_int($resource))
			$where_clause = "tr.id = $resource";
		else {
			$resource = $wpdb->escape($resource);
			$where_clause = "tr.rs_md5 LIKE MD5('{$resource}')";
		}
		$query = "SELECT * FROM {$SlimCfg->table_resource} tr WHERE $where_clause AND (tr.rs_title <> '' OR tr.rs_condition <> '') LIMIT 1";
		if ($_pre = $wpdb->get_row($query)) {
			return $_pre;
		}
		return false;
	}

	function _insertPostTitle($title='', $condition='', $resource) {
		global $wpdb, $SlimCfg;
		$_resource = $wpdb->escape($resource);
		$_title = $wpdb->escape($title);
		$_condition = $wpdb->escape($condition);
		$insert = $wpdb->query("UPDATE {$SlimCfg->table_resource} tr SET tr.rs_title = '{$_title}', tr.rs_condition = '{$_condition}' WHERE tr.rs_md5 = MD5(TRIM('{$_resource}')) LIMIT 1");
		return $insert;
	}

	function _getPostTitle(&$query_vars, $before, $after, $resource, $track=false, $did_permalink=false, $dev=false) {
		global $SlimCfg, $wp_query, $wp_the_query;
		$returnarr = $track || $dev;
		if ($track) {
			$_query =& $wp_the_query;
		} else {
			$_query =& new WP_Query($query_vars);
			// FROM WP::handle_404()
			// Issue a 404 if a permalink request doesn't match any posts.  Don't
			// issue a 404 if one was already issued, if the request was a search,
			// or if the request was a regular query string request rather than a
			// permalink request.
			if ( (0 == count($_query->posts)) && !$_query->is_404 && !$_query->is_search && ( $did_permalink || (!empty($query_string) && (false === strpos($resource, '?'))) ) ) {
				$_query->set_404();
			}
		}

		$_type = $_job = $_title = '';
		$_queried_object =& $_query->get_queried_object();
		$_queried_object_id =& $_query->get_queried_object_id();

		if (has_filter('slimstat_post_title')) {
			$extra = apply_filters('slimstat_post_title', &$_query);
			if ( $extra && is_array($extra) && !empty($extra['post_title']) ) {
				return '';
			}
		}

		if ($_query->is_trackback && $_queried_object_id)
			$_job = '[trackback]';
		elseif ($_query->is_feed)
			$_job = '[feed]';
		elseif ($_query->is_paged)
			$_type = '[paged]';

		// refer to wp-inlcudes/query.php get_queried_object()
		if ($_query->is_404) {
			$_type = '[404 error]';
		} elseif ($_query->is_category) {
			$_title = $_queried_object->cat_name;
			$_type = '[category]';
		} elseif ($_query->is_tag) {
			$_title = $_query->get('tag');
			$_type = '[tag]';
		} elseif ($_query->is_tax) {
			$_title = $_queried_object->taxonomy ? '['.$_queried_object->taxonomy.'] ' : '';
			$_title .= $_queried_object->name;
			$_type = '[taxonomy]';
		} elseif ($_query->is_comment_feed) {
			if ($_query->is_singular)
				$_title = $_queried_object->post_title;
			if ($_query->is_attachment)
				$_type = '[attachment comments]';
			elseif ($_query->is_single)
				$_type = '[post comments]';
			elseif ($_query->is_page)
				$_type = '[page comments]';
			else 
				$_type = '[comments]';
		} elseif ($_query->is_posts_page) {
			$_title = $_queried_object->post_title;
			$_type = '[posts page]';
		} elseif ($_query->is_attachment) {
			$_title = $_queried_object->post_title;
			$_title .= ('' != $_title && $_queried_object->post_mime_type) ? ' ('.$_queried_object->post_mime_type.')' : '';
			$_type = '[attachment]';
		} elseif ($_query->is_page || $_query->is_single) {
			$_title = $_queried_object->post_title;
			$_type = ($_query->is_page)?'[page]':'[post]';
		} elseif ($_query->is_date) {
			$_type = '[date]';
		} elseif ($_query->is_search) {// maybe useless...
			$_title = $_query->get('s');
			$_type = '[search]';
		} elseif ($_query->is_author) {
			$_title = $_queried_object->display_name;
			$_type = '[author]';
		} elseif ($_query->is_comments_popup) {
			$_type = '[comments popup]';
		} elseif ($_query->is_home) {
			$_type = '[home]';
		}
		$_title = trim($_title);
		if (!$_title || '' == $_title) $_title = '';
		if ('' == $_title && '' == $_job && '' == $_type)
			$_job = '[unusual]';
		if ($_type == "" && $_job == "" ) {
			$before = $after = "";
		}

		if ($returnarr)
			return array('title'=>$_title, 'job'=>$_job, 'type'=>$_type, 'obj_id'=>(int)$_queried_object_id);

		$insert = SSFunction::_insertPostTitle($_title, $_job.$_type, $resource);
		// translate job and type
		if ('' != $_type) $_type = __($_type, SLIMSTAT_DOMAIN);
		if ('' != $_job) $_job = __($_job, SLIMSTAT_DOMAIN);
		return $before.$_job.$_type.$after.' '.wp_specialchars($SlimCfg->trimString($_title, 68), true);
	}

	// To do... or remove
	function print_pages($query, $rows, $pinid) {
		global $wpdb, $SlimCfg;
		$offset = $SlimCfg->get['slim_offset'];
		$use_ajax = $SlimCfg->option['use_ajax'];
		$location = get_option('siteurl').'/wp-admin/index.php?page=wp-slimstat-ex';

//		$query = "SELECT COUNT(DISTINCT ts.visit) AS counts FROM $SlimCfg->table_stats ts WHERE $filter_clause";
		$counts = (int)$wpdb->get_var($query);
		$count_pages = ceil($counts / $rows);
		if ($count_pages > 1) {
			$count_pages = ($count_pages>20)?20:$count_pages;
			$pnav = '<form method="get" action="'.($use_ajax ? $SlimCfg->pluginURL.'/lib' : $location).'" id="page_navi"';
			$pnav .= ($use_ajax ? ' onchange="SlimStat.nav();return false;" onsubmit="SlimStat.nav();return false;"' : '').'>'; 
			$pnav .= $use_ajax ? '' : '<input type="hidden" name="page" value="wp-slimstat-ex" />';
			$pnav .= '<div class="page-navi">Pages : <select name="slim_offset">';
			for($i=0;$i<$count_pages;$i++) {
				$pnav .= '<option value="'.$i.'"'.(($offset == $i )?'  selected="selected"':'').'>'.($i+1).'&nbsp;</option>';
			}
			$pnav .= '</select>';
			if (isset($SlimCfg->get['fi'])) {
				$pnav .= '<input type="hidden" name="ff" value="'.$SlimCfg->get['ff'].'" />';
				$pnav .= '<input type="hidden" name="fi" value="'.$SlimCfg->get['fi'].'" />';
				$pnav .= '<input type="hidden" name="ft" value="'.$SlimCfg->get['ft'].'" />';
			}
			if (isset($SlimCfg->get['fd'])) {
				$pnav .= '<input type="hidden" name="fd" value="'.$SlimCfg->get['fd'].'" />';
			}
			$pnav .= '<input type="hidden" name="panel" value="'.$SlimCfg->get['pn'].'" />';
			$pnav .= ($use_ajax ? '<!--[if IE]>' : '').'&nbsp;<input type="submit" name="go_page" id="go_page" value="go" />'.($use_ajax ? '<![endif]-->' : '');
			$pnav .= '</div></form>';
			return $pnav;
		}
		return '';
	}

	function _getTableSize($table='common') {
		global $wpdb, $SlimCfg;
		switch($table) {
			case 'feed':
				$_table = $SlimCfg->table_feed;
			break;
			case 'dt':
				$_table = $SlimCfg->table_dt;
			break;
			case 'common':
				$_table = $SlimCfg->table_stats;
			break;
			default:
				$_table = $table;
			break;
		}
		$query = "SHOW TABLE STATUS LIKE '{$_table}' ";
		if ( $table_details = $wpdb->get_row($query, ARRAY_A, 0) ) {
			$table_size = ( $table_details["Data_length"] / 1024 ) + ( $table_details["Index_length"] / 1024 );
			return number_format($table_size, 0, ".", ",")." Kbyte";
		}
		return 0;
	}

	function ajax_response_headers($type='html') {
		global $SlimCfg;
		$cachelimit = (int) $SlimCfg->option['cachelimit'];
		if($cachelimit > 0) {
			// check to see if the user has enabled gzip compression in the WordPress admin panel
			if ( extension_loaded('zlib') and !ini_get('zlib.output_compression') and ini_get('output_handler') != 'ob_gzhandler' and ((version_compare(phpversion(), '5.0', '>=') and ob_get_length() == false) or ob_get_length() === false) ) {
				ob_start('ob_gzhandler');
			}

			$cache_offset = 60*$cache_limit;
			@header("Cache-Control: public");
			@header("Pragma: cache");
			@header("Expires: ".gmdate("D, d M Y H:i:s",time() + $cache_offset)." GMT");
			@header("Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT");
		} else {
			nocache_headers();
		}
		switch($type) {
			case 'json':
				$content_type = 'text/x-json';
			break;
			case 'html': default:
				$content_type = 'text/html';
			break;
		}
		@header('Content-Type: '.$content_type.'; charset: '.get_option('blog_charset').'');
	}

}//end of class
?>
