<?php 
if ( !defined('SLIMSTATPATH') ) {
 die('Can not find Wp-SlimStat. Please activate plugin');
}
load_plugin_textdomain('slimstat-admin', 'wp-content/plugins/'.$SlimCfg->basedir.'/lang');

if(!class_exists('SSAdmin')) :
class SSAdmin extends SlimCfg {
	var $old_stats;
	var $table_shortstat;
	var $platformString2ID;
	var $browserString2ID;
	var $country2code;
	
	function SSAdmin() {
		global $table_prefix;
		$this->_init();
		// We also use some tables from Wordpress default set
		$this->old_stats = $table_prefix . "slim_stats";
		$this->table_shortstat = $table_prefix . "ss_stats";
		
		// Let's define some useful codes
		$this->platformString2ID = array('windows 2000' => '0','windows xp' => '1','windows 2003' => '2','windows vista' => '3','windows nt' => '4','windows 98' => '5','windows 9x' => '5','windows 95' => '6','windows 3.1' => '7','windows' => '5','mac 680' => '9','mac 68k' => '9','mac os x' => '10','mac ppc' => '11','mac' => '12','os2' => '13','sun os' => '14','unix irix' => '15','hp unix' => '16','aix' => '17','dec alpha' => '18','vax' => '19','linux' => '20','free bsd' => '21','indeterminable' => '-1','xx' => '-1');
		
		$this->browserString2ID = array('mozilla' => '0','netscape' => '1','safari' => '2','icab' => '3','firefox' => '4','firebird' => '5','phoenix' => '6','camino' => '7','chimera' => '8','internet explorer' => '9','msn explorer' => '10','wordpress' => '11','blogsearch engine' => '12','allblog.net rsssync' => '13','hanrss' => '14','blogging client' => '15','w3c html validator' => '16','atom rss validator' => '17','w3c css validator' => '18','python-urllib' => '19','newsgator' => '20','google desktop' => '21','java' => '22','aol browser' => '24','aol' => '23','k-meleon' => '25','beonex' => '26','opera' => '27','omniweb' => '28','konqueror' => '29','galeon' => '30','epiphany' => '31','kazehakase' => '32','amaya' => '33','crawler' => '34','lynx' => '35','elinks' => '37','links' => '36','thunderbird' => '38','flock' => '39','libwww perl library' => '40','apache bench tool' => '41','seamonkey' => '42','mediapartners-google' => '43','feedfetcher-google' => '44','googlebot' => '45','msnbot' => '46','yahoo-blogs' => '47','gigabot' => '48','zyborg' => '49','nutchcvs' => '50','ichiro' => '51','technoratibot' => '52','heritrix' => '53','indeterminable' => '-1','xx' => '-1', 'crawler/search engine' => '34', 'Chrome' => '190', 'Chromium' => '191');

		$this->country2code = array("andorra" => "ad", "united arab emirates" => "ae", "afghanistan" => "af", "antigua and barbuda" => "ag", "anguilla" => "ai", "albania" => "al", "armenia" => "am", "netherlands antilles" => "an", "angola" => "ao", "antarctica" => "aq", "argentina" => "ar", "american samoa" => "as", "austria" => "at", "australia" => "au", "aruba" => "aw", "azerbaijan" => "az", "bosnia and herzegovina" => "ba", "barbados" => "bb", "bangladesh" => "bd", "belgium" => "be", "bulgaria" => "bg", "bahrain" => "bh", "burundi" => "bi", "benin" => "bj", "bermuda" => "bm", "brunei darussalam" => "bn", "bolivia" => "bo", "brazil" => "br", "bahamas" => "bs", "bhutan" => "bt", "botswana" => "bw", "belarus" => "by", "belize" => "bz", "canada" => "ca", "the democratic republic of the congo" => "cd", "central african republic" => "cf", "congo" => "cg", "switzerland" => "ch", "cote divoire" => "ci", "cook islands" => "ck", "chile" => "cl", "cameroon" => "cm", "china" => "cn", "colombia" => "co", "costa rica" => "cr", "serbia and montenegro" => "cs", "cuba" => "cu", "cape verde" => "cv", "cyprus" => "cy", "czech republic" => "cz", "germany" => "de", "djibouti" => "dj", "denmark" => "dk", "dominica" => "dm", "dominican republic" => "do", "algeria" => "dz", "ecuador" => "ec", "estonia" => "ee", "egypt" => "eg", "eritrea" => "er", "spain" => "es", "ethiopia" => "et", "finland" => "fi", "fiji" => "fj", "falkland islands malvinas" => "fk", "federated states of micronesia" => "fm", "faroe islands" => "fo", "france" => "fr", "gabon" => "ga", "united kingdom" => "gb", "grenada" => "gd", "georgia" => "ge", "french guiana" => "gf", "ghana" => "gh", "gibraltar" => "gi", "greenland" => "gl", "gambia" => "gm", "guinea" => "gn", "guadeloupe" => "gp", "equatorial guinea" => "gq", "greece" => "gr", "guatemala" => "gt", "guam" => "gu", "guineabissau" => "gw", "guyana" => "gy", "hong kong" => "hk", "honduras" => "hn", "croatia" => "hr", "haiti" => "ht", "hungary" => "hu", "indonesia" => "id", "ireland" => "ie", "israel" => "il", "india" => "in", "british indian ocean territory" => "io", "iraq" => "iq", "islamic republic of iran" => "ir", "iceland" => "is", "italy" => "it", "jamaica" => "jm", "jordan" => "jo", "japan" => "jp", "kenya" => "ke", "kyrgyzstan" => "kg", "cambodia" => "kh", "kiribati" => "ki", "comoros" => "km", "saint kitts and nevis" => "kn", "republic of korea" => "kr", "kuwait" => "kw", "cayman islands" => "ky", "kazakhstan" => "kz", "lao peoples democratic republic" => "la", "lebanon" => "lb", "saint lucia" => "lc", "liechtenstein" => "li", "sri lanka" => "lk", "liberia" => "lr", "lesotho" => "ls", "lithuania" => "lt", "luxembourg" => "lu", "latvia" => "lv", "libyan arab jamahiriya" => "ly", "morocco" => "ma", "monaco" => "mc", "republic of moldova" => "md", "madagascar" => "mg", "marshall islands" => "mh", "the former yugoslav republic of macedonia" => "mk", "mali" => "ml", "myanmar" => "mm", "mongolia" => "mn", "macao" => "mo", "northern mariana islands" => "mp", "martinique" => "mq", "mauritania" => "mr", "malta" => "mt", "mauritius" => "mu", "maldives" => "mv", "malawi" => "mw", "mexico" => "mx", "malaysia" => "my", "mozambique" => "mz", "namibia" => "na", "new caledonia" => "nc", "niger" => "ne", "norfolk island" => "nf", "nigeria" => "ng", "nicaragua" => "ni", "netherlands" => "nl", "norway" => "no", "nepal" => "np", "nauru" => "nr", "niue" => "nu", "new zealand" => "nz", "oman" => "om", "panama" => "pa", "peru" => "pe", "french polynesia" => "pf", "papua new guinea" => "pg", "philippines" => "ph", "pakistan" => "pk", "poland" => "pl", "puerto rico" => "pr", "palestinian territory occupied" => "ps", "portugal" => "pt", "palau" => "pw", "paraguay" => "py", "qatar" => "qa", "reunion" => "re", "romania" => "ro", "russian federation" => "ru", "rwanda" => "rw", "saudi arabia" => "sa", "solomon islands" => "sb", "seychelles" => "sc", "sudan" => "sd", "sweden" => "se", "singapore" => "sg", "slovenia" => "si", "slovakia" => "sk", "sierra leone" => "sl", "san marino" => "sm", "senegal" => "sn", "somalia" => "so", "suriname" => "sr", "sao tome and principe" => "st", "el salvador" => "sv", "syrian arab republic" => "sy", "swaziland" => "sz", "chad" => "td", "french southern territories" => "tf", "togo" => "tg", "thailand" => "th", "tajikistan" => "tj", "tokelau" => "tk", "timorleste" => "tl", "turkmenistan" => "tm", "tunisia" => "tn", "tonga" => "to", "turkey" => "tr", "trinidad and tobago" => "tt", "tuvalu" => "tv", "taiwan" => "tw", "united republic of tanzania" => "tz", "ukraine" => "ua", "uganda" => "ug", "united states" => "us", "uruguay" => "uy", "uzbekistan" => "uz", "holy see vatican city state" => "va", "saint vincent and the grenadines" => "vc", "venezuela" => "ve", "virgin islands british" => "vg", "virgin islands us" => "vi", "viet nam" => "vn", "vanuatu" => "vu", "samoa" => "ws", "yemen" => "ye", "mayotte" => "yt", "south africa" => "za", "zambia" => "zm", "zimbabwe" => "zw","indeterminable"=>"","--"=>"", "xx"=>"", "south korea"=>"kr");
	}

	function _go() {
		$go = false;
		if( isset($_POST['sstep']) && $_POST['sstep'] == 'go' )
			$go = true;
		return $go;
	}

	function _isStatTableDetected($check="shortstat") {
		global $wpdb;
		
		$isStatTableDetected = false;
		switch($check) {
			case 'oldslim':
				$_table = $this->old_stats;
			break;
			case 'shortstat':
			default:
				$_table = $this->table_shortstat;
			break;
		}
		foreach ( $wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ( $table == $_table ) {
				$isStatTableDetected = true;
			}
		}
		return $isStatTableDetected;
	}
	// end isStatTableDetected

	function _beforeImport() {
		global $wpdb;

		if(!isset($_POST['convert'])) {
		$myAddBrowserAndPlatform = 
			"ALTER TABLE ".$this->table_shortstat."
				ADD platform_slim TINYINT DEFAULT '-1' AFTER platform,
				ADD browser_slim SMALLINT DEFAULT '-1' AFTER browser,
				ADD INDEX referer_smartidx (referer),
				ADD INDEX browser_smartidx ( browser ( 10 ) ),
				ADD INDEX platform_smartidx ( platform ( 10 ) )";
			
		if ( $wpdb->query( $myAddBrowserAndPlatform ) === false ) {
			return false;
		} 
?>
<p><?php _e('needed columns and index keys added, now convert platforms', 'slimstat-admin'); ?></p>
<form action="short2slim.php?step=2" method="post">
<input type="hidden" name="shortup" value="go" />
<input type="hidden" name="country" value="done" />
<input type="hidden" name="convert" value="pf" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Convert Platforms', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php
		} else if ($_POST['convert'] == 'pf') {

		foreach( $this->platformString2ID as $platform_name => $platform_id ) {
			$mySelectNames = 
				"SELECT COUNT( * ) FROM ".$this->table_shortstat."
						WHERE LOWER( platform ) LIKE '%".$platform_name."%'";
		
			$myCountRows = $wpdb->get_var( $mySelectNames, 0 , 0 );
		
			if ( $myCountRows > 0 && $myCountRows !== false ) {
				$myUpdateNames =
					"UPDATE ".$this->table_shortstat." SET platform_slim = '".$platform_id."'
						WHERE LOWER( platform ) LIKE '%".$platform_name."%'";
				if ( $wpdb->query( $myUpdateNames ) === false ) {
					return false;
				}
			}
		}//end convert platform		
?>
<p><?php _e('platform conversion is complete. now convert browsers', 'slimstat-admin'); ?></p>
<form action="short2slim.php?step=2" method="post">
<input type="hidden" name="shortup" value="go" />
<input type="hidden" name="country" value="done" />
<input type="hidden" name="convert" value="br" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Convert browsers', 'slimstat-admin'); ?> &raquo;" /></p>
</form>

<?php } else if ($_POST['convert'] == 'br') {
		foreach( $this->browserString2ID as $browser_name => $browser_id ) {
			$mySelectNames = 
				"SELECT COUNT( * ) FROM ".$this->table_shortstat."
					WHERE LOWER( browser ) LIKE '%".$browser_name."%'";
			
			$myCountRows = $wpdb->get_var( $mySelectNames, 0 , 0 );
			
			if ( $myCountRows > 0 && $myCountRows !== false ) {
				$myUpdateNames =
					"UPDATE ".$this->table_shortstat." SET browser_slim = '".$browser_id."'
						WHERE LOWER( browser ) LIKE '%".$browser_name."%'";
				if ( $wpdb->query( $myUpdateNames ) === false ) {
					return false;
				}
			}
		}//end of convert browser
?>
<p><?php _e('browser conversion is complete.', 'slimstat-admin'); ?></p>
<p><?php _e('Ok, now go to next step', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="short2slim.php?step=3"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php 
	}
	}//end of beforeImport

	function fix_resource_column_for_oldstats($table='common') {
		global $wpdb;
		require_once(SLIMSTATPATH . 'lib/upgrade.php');
		$_table = ('feed' == $table) ? $this->table_feed : $this->table_stats;
		$exists = $this->maybe_add_column($_table, 'resource', '', true);
		$exists2 = $this->maybe_add_column($_table, 'rs_id', '', true);
		if($exists && $exists2)
			return true;
		if($exists) {
			$query = "ALTER TABLE $_table CHANGE resource rs_id INT(11) NOT NULL default 0 ";
			if($wpdb->query($query) === false) {
				return false;
			}
		}
		$exists = $this->maybe_add_column($_table, 'resource', '', true);
		if(!$exists) {
			$query = "ALTER TABLE $_table ADD resource varchar(255) NOT NULL default '' AFTER rs_id";
			if($wpdb->query($query) === false) {
				return false;
			}
		}
		return true;
	}
	
	function _importShortStat() {
		global $wpdb;
		$query = "TRUNCATE TABLE ".$this->table_stats." ";
		if($wpdb->query($query) === false) {
			return false;
		}
		$query = "TRUNCATE TABLE ".$this->table_feed." ";
		if($wpdb->query($query) === false) {
			return false;
		}
		$fix_column = $this->fix_resource_column_for_oldstats('common');
		if(!$fix_column)
			return false;
		$fix_column = $this->fix_resource_column_for_oldstats('feed');
		if(!$fix_column)
			return false;
		$myCopyShortToSlim = 
			"INSERT INTO $this->table_stats (
				remote_ip,
				language,
				country,
				domain,
				referer,
				resource,
				platform,
				browser,
				version,
				dt			
			)
			SELECT 
				INET_ATON(remote_ip), 
				REPLACE(REPLACE(language, 'empty', ''), '_', '-') AS language,
				country_slim,
				domain,
				REPLACE(referer, 'http://', '') AS referer,
				resource,
				platform_slim,
				browser_slim,
				REPLACE(LOWER(version), 'indeterminable', '') AS version,
				dt
			FROM $this->table_shortstat";

		if ( $wpdb->query( $myCopyShortToSlim ) === false ) {
			return false;
		}
		return true;
	}
	// end importShortStat

	function _importSlimStat() {
		global $wpdb;
		$query = "TRUNCATE TABLE ".$this->table_stats." ";
		if($wpdb->query($query) === false) {
			return false;
		}
		$query = "TRUNCATE TABLE ".$this->table_feed." ";
		if($wpdb->query($query) === false) {
			return false;
		}
		$fix_column = $this->fix_resource_column_for_oldstats('common');
		if(!$fix_column)
			return false;
		$fix_column = $this->fix_resource_column_for_oldstats('feed');
		if(!$fix_column)
			return false;
		$myCopySlimToEx = 
			"INSERT INTO $this->table_stats (
				remote_ip,
				language,
				country,
				domain,
				referer,
				searchterms,
				resource,
				platform,
				browser,
				version,
				dt			
			)
			SELECT 
				remote_ip,
				language,
				country,
				domain,
				referer,
				searchterms,
				resource,
				platform,
				browser,
				version,
				dt			
			FROM $this->old_stats";

		if ( $wpdb->query( $myCopySlimToEx ) === false ) {
			return false;
		}
		return true;
	}
	// end importSlimStat

	function set_dt_data($custom ='', $type = 0) {
		if($custom == "") $custom = 1;
		switch($type) {
			case 2: case 12:
				$typeN = 'feed';
			break;
			case 3: case 13:
				$typeN = 'all';
			break;
			case 1: case 11: default:
				$typeN = 'common';
			break;
		}
		$max_age = $this->midnight_print - ( $custom * 86400 );
		$max_age_db = $this->midnight_db - ( $custom * 86400 );
		$first_hit = SSFunction::get_firsthit($typeN);
		if ( $max_age_db > $first_hit ) {
//		set_time_limit(120);// increase time limit of PHP execution
			print "<p>Inserting ";
			$dt = $this->mktime(array('h'=>0, 'i'=>0, 's'=>0), $this->time_switch($first_hit, 'blog'));
			while ( $dt < $max_age ) {
			// hour
				unset($dt_db);
				unset($dt_end);
				unset($hvu);
//				if($dt > 1157036400) :
				$dt_db = $this->time_switch($dt, 'db');
//				$dt_end = mktime( date( "H", $dt ), 59, 59, date( "n", $dt ), date( "d", $dt ), date( "Y", $dt ) );
//				$dt_end = $this->sstime($dt_end, true);
				$dt_end = $this->mktime(array('i'=>59, 's'=>59), $dt, 'db');
				$hvu = SSFunction::ins_dt( $dt_db, $dt_end, $type );
//				endif;
				print ". ".$this->date( "H", $dt );
				if ( $this->date( "H", $dt ) == 0 ) {
					echo '..'.$this->date("M j", $dt).'('.$dt.')..';/*
					// day
					$dt_end = mktime( date( "H", $dt ) - 1, 59, 59, date( "n", $dt ), date( "d", $dt ) + 1, date( "Y", $dt ) );
					$dt_end = $this->sstime($dt_end, true);
					$hvu = SSFunction::ins_dt( $dt_db, $dt_end, $type );
					print ". ";
					// week
					$dt_end = mktime( date( "H", $dt ) - 1, 59, 59, date( "n", $dt ), date( "d", $dt ) + 7, date( "Y", $dt ) );
					$dt_end = $this->sstime($dt_end, true);
					$hvu = SSFunction::ins_dt( $dt_db, $dt_end, $type );
					print ". ";
					// month
					$dt_end = mktime( date( "H", $dt ) - 1, 59, 59, date( "n", $dt ) + 1, date( "d", $dt ), date( "Y", $dt ) );
					$dt_end = $this->sstime($dt_end, true);
					$hvu = SSFunction::ins_dt( $dt_db, $dt_end, $type );
					print ". ";*/
				}
				$dt += 60 * 60;
//				$dt = strtotime( date( "Y-m-d H:00:00", $dt ) );
				$dt = $this->mktime(array('i'=>0, 's'=>0), $dt);
			}
			print "</p><h2>OK, done!</h2>\n";
			return true;
		} else {
			?>
			<p><?php _e('The oldest entry in the database is from', 'slimstat-admin'); ?> <?php print $this->date( "j M Y", $this->time_switch(SSFunction::get_firsthit(), 'blog') ); ?>.</p>
			<p><?php _e('SlimStat is configured to delete data more than', 'slimstat-admin'); ?> <?php print $this->option['dbmaxage']; ?> <?php _e('days old, or from before', 'slimstat-admin'); ?> <?php print $this->date( "j M Y", $max_age ); ?>.</p>
			<p><?php _e('To delete more data, change <strong>DB max-age:</strong> on SlimStat option panel', 'slimstat-admin'); ?></p>
			<?php return false;
		}
	}// end set_dt_data

	function _determineVisit( $_remote_ip, $_browser, $dt, $_table = '' ) {
		global $wpdb;
		if(empty($_table)) $_table = $this->table_stats;
		$query = "SELECT visit FROM {$_table} ";
		$query .= " WHERE remote_ip= {$_remote_ip} "; 
		$query .= "	AND browser= {$_browser} "; 
		$query .= "	AND dt >= ({$dt} - 1800) "; 
		$query .= "	AND dt < {$dt} "; 
		$query .= "	ORDER BY dt LIMIT 1 ";
		if ( $row = $wpdb->get_row( $query ) ) {//echo '<br />exists';print_r($row);
			return $row->visit;
		}
		$query = "SELECT MAX(visit) AS visit FROM {$_table} ";
		if ( $row = $wpdb->get_row( $query ) ) {//echo '<br />new';print_r($row);
			return ($row->visit + 1 );
		}
		return 1;
	}

	function update_visit($_table = '') {
		global $wpdb;
		set_time_limit(300);
		if(empty($_table)) $_table = $this->table_stats;
		$limit = 50000;
		$dboffset = (isset($_POST["offset"]) && !empty($_POST["offset"])) ? (int)$_POST["offset"] : 0;
		if(isset($_POST['offset'])) {
			$start = ($dboffset-1)*$limit;
			$query = "SELECT id, remote_ip, browser, visit, dt 
				FROM {$_table} ORDER BY dt ASC 
				LIMIT {$start}, {$limit} ";
			if($rs = $wpdb->get_results($query)) {
//				$optimize = $wpdb->query("OPTIMIZE TABLE $_table ");
					print '<span>updating...</span><span class="dot">';
				foreach($rs as $r) {
					if((int)$r->visit != 0)
						continue;
					$visit = $this->_determineVisit($r->remote_ip, $r->browser, $r->dt, $_table);
					$q = "UPDATE {$_table} SET visit = {$visit} WHERE id = {$r->id} ";
					$update = $wpdb->query($q);	print '. ';				
				}
				print '</span>';
				print '<h2>Done, '.($dboffset*$limit).' lines inserted till now</h2>';
				if(count($rs) !==0 && count($rs) < $limit) {
				if( $_table == $this->table_feed ) { 
		?>
		<h3><?php _e('Update Finished', 'slimstat-admin'); ?></h3>
		<p><?php _e('All visit data inserted, now we will insert dt(sum) data to dt table', 'slimstat-admin'); ?><p>
		<h2 class="step"><a href="slim2ex.php?step=5"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php return;
					} else {
		?>
		<h3><?php _e('Update Finished', 'slimstat-admin'); ?></h3>
		<p><?php _e('Visit data inserted, now we will insert visit data to feed table', 'slimstat-admin'); ?><p>
		<form action="slim2ex.php?step=4" method="post">
		<input type="hidden" name="sstep" value="go" />
		<input type="hidden" name="fvisit" value="go" />
		<p class="submit"><input type="submit" value="<?php _e('Insert Visit Data To Feed Table', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php return;
					}
				}
			} else {
		?>
		<h3><?php _e('Data not found', 'slimstat-admin'); ?></h3>
		<p><?php _e('maybe you don\'t have any data and don\'t need to do this step', 'slimstat-admin'); ?><p>
		<p><?php _e('go to next step, we will import feed data to feed table', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="slim2ex.php?step=5"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php return;
			}
		}
		if( $_table == $this->table_feed ) $now = __('Inserting visit data to feed stats table', 'slimstat-admin').'(<em>'.$this->table_feed.'</em>)';
		else $now = __('Inserting visit data to common stats table', 'slimstat-admin').'(<em>'.$this->table_stats.'</em>)';
		?>
<script type="text/javascript">
function disable_substep_submit(el) {
	el.disabled = 'true';
	return true;
}
</script>
		<p><?php echo $now; ?></p>
		<h2 style="color:red;"><?php _e('NOTE: Please wait until "Done, ...." message appear', 'slimstat-admin'); ?></h2>
		<?php $message = ($dboffset == 0)?__('To start insert visit data Click "Next ', 'slimstat-admin').$limit.__('" button', 'slimstat-admin') : __('Ok, now we will insert database ', 'slimstat-admin').(($dboffset*$limit)+1).' ~ '.(($dboffset+1)*$limit).__(' lines', 'slimstat-admin'); ?>
		<h4 style="color:green;"><?php echo $message; ?></h4>
		<form action="slim2ex.php?step=4" method="post">
		<input type="hidden" name="offset" value="<?php echo $dboffset+1; ?>" />
		<input type="hidden" name="sstep" value="go" />
		<?php if($_table == $this->table_feed) echo '<input type="hidden" name="fvisit" value="go" />'; ?>
		<p class="submit"><input type="submit" onclick="disable_substep_submit(this);" value="<?php _e('Next', 'slimstat-admin'); ?> <?php echo $limit; ?> &raquo;" /></p>
		</form>
		<?php
	}

	function _ss_reset_columns() {
		global $wpdb;
	
		$myTableStructure = $wpdb->get_results("SHOW COLUMNS FROM $this->table_shortstat", ARRAY_A);
		
		foreach ( $myTableStructure as $field_details ) {
			if ( $field_details['Field'] == 'country_slim') {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP country_slim";
				if($wpdb->query($query) === false) return false;
			}
			elseif ( $field_details['Field'] == 'platform_slim') {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP platform_slim";
				if($wpdb->query($query) === false) return false;
			}
			elseif ( $field_details['Field'] == 'browser_slim') {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP browser_slim";
				if($wpdb->query($query) === false) return false;
			}
		}
		return true;
	}

	function _ss_reset_index() {
		global $wpdb;
		
		$myIndexStructure = $wpdb->get_results("SHOW INDEX FROM $this->table_shortstat", 'ARRAY_A');
		foreach ( $myIndexStructure as $index_details ) {
			if ( $index_details['Key_name'] == 'country_smartidx' ) {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP INDEX country_smartidx ";
				if($wpdb->query($query) === false) return false;
			}
			elseif ( $index_details['Key_name'] == 'platform_smartidx' ) {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP INDEX platform_smartidx ";
				if($wpdb->query($query) === false) return false;
			}
			elseif ( $index_details['Key_name'] == 'referer_smartidx' ) {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP INDEX referer_smartidx ";
				if($wpdb->query($query) === false) return false;
			}
			elseif ( $index_details['Key_name'] == 'browser_smartidx' ) {
				$query = "ALTER TABLE ".$this->table_shortstat." DROP INDEX browser_smartidx ";
				if($wpdb->query($query) === false) return false;
			}
		}
		return true;
	}

	function _indexingList() {
		global $wpdb;
		$indexingList = '';
		$myIndexStructure = $wpdb->get_results("SHOW INDEX FROM $this->table_stats", 'ARRAY_A');
		foreach ( $myIndexStructure as $index_details ) {
			if ( $index_details['Key_name'] == 'language_idx' ) 
				$indexingList .= '#language_idx#';
			elseif ( $index_details['Key_name'] == 'country_idx' ) 
				$indexingList .= '#country_idx#';
			elseif ( $index_details['Key_name'] == 'domain_idx' ) 
				$indexingList .= '#domain_idx#';
			elseif ( $index_details['Key_name'] == 'referer_idx' ) 
				$indexingList .= '#referer_idx#';
			elseif ( $index_details['Key_name'] == 'searchterms_idx' ) 
				$indexingList .= '#searchterms_idx#';
			elseif ( $index_details['Key_name'] == 'resource_idx' ) 
				$indexingList .= '#resource_idx#';
			elseif ( $index_details['Key_name'] == 'remote_ip_idx' ) 
				$indexingList .= '#remote_ip_idx#';
			elseif ( $index_details['Key_name'] == 'browser_idx' ) 
				$indexingList .= '#browser_idx#';
			elseif ( $index_details['Key_name'] == 'platform_idx' ) 
				$indexingList .= '#platform_idx#';
		}
		return $indexingList;
	}
	// end indexingList

	function _indexTable($table = 'common') {
		$step = ($table == 'common')?1:2;
//		$allKeys = array('dt', 'remote_ip', 'resource', 'referer', 'visit', 'domain', 'country', 'browser', 'searchterms', 'platform', 'language');
		$columns = array( 'dt_total' => 'remote_ip, dt, visit', 'resource_total' => 'resource');//, dt, id' );
		$allKeys = array( 
			'dt' => __('Related to almost modules. <strong>recommended</strong>.', 'slimstat-admin'), 
			'remote_ip' => __('Related to almost modules. <strong>recommended</strong>.', 'slimstat-admin'), 
			'resource' => __('Related to almost modules. <strong>recommended</strong>.', 'slimstat-admin'), 
			'referer' => __('Top referers, Recent domains , Top domains , New domains', 'slimstat-admin'), 
			'searchterms' => __('Recent, Top search string module.', 'slimstat-admin'), 
			'domain' => __('Recent, Top, New domains module.', 'slimstat-admin'), 
			'browser' => __('Browser modules', 'slimstat-admin'), 
			'platform' => __('Platform module', 'slimstat-admin'),
			'language' => __('Language module', 'slimstat-admin'),
			'visit' => __('Related to visit count modules', 'slimstat-admin'),
		);
		$defaults = array('dt', 'remote_ip', 'resource');
		$current = $this->_getIndexKeys($table);
//		print_r($current);return;
		$table = '<table class="widefat" cellpadding="3" cellspacing="3">';
		$table .= "<thead>\n<tr>\n";
		$table .= "<th>Name</th>";
		$table .= "<th>Column</th>";
		$table .= "<th>Related</th>";
		$table .= "<th>Action</th>\n";
		$table .= "</tr>\n</thead>\n<tbody>\n";
		foreach ($allKeys as $keyrow=>$related) {
			if(in_array($keyrow, $current)) {
				$add_remove = 'remove';
				$class = 'active';
				$class_ar = 'delete';
			} else {
				$add_remove = 'add';
				$class = 'unused';
				$class_ar = 'edit';
			}
			$class_row = ($class_row == ' alternate')?'':' alternate';
			$table .= "<tr class=\"".$class.$class_row."\">\n";
			$table .= '<td class="name">'.$keyrow.'</td>';
			$table .= '<td class="ver">'.((isset($columns[$keyrow]))?$columns[$keyrow]:$keyrow).'</td>';
			$table .= '<td class="desc">'.$related.'</td>';
			$table .= '<td class="togl">';
			if(!in_array($keyrow, $defaults))
				$table .= '<a class="'.$class_ar.'" href="performance.php?step='.$step.'&amp;job='.$add_remove.'&amp;key='.$keyrow.'">'.ucfirst(__($add_remove, 'slimstat-admin')).'</a>';
			else 
				$table .= '<a class="'.$class_ar.'" href="javascript:void(0);">N/A</a>';
			$table .= '</td>';
			$table .= "</tr>\n";
		}
		$table .= "</tbody>\n</table>\n";
		return $table;
	}

	function _do_indexing($job, $key, $table, $echo=true, $index_type="INDEX") {
		global $wpdb;
		if( $job == "" || $key == "" || $table == "" ) {
			if($echo)
				echo '<div class="updated fade"><p>'.__('Required values are not defined', 'slimstat-admin').'</p></div>';
			return false;
		}
		$column_array = array( 'dt_total' => '(remote_ip, dt, visit)', 'resource_total' => '(resource)', 'rs_md5_site' => '(rs_md5, site_id)' );
		$columns = (isset($column_array[$key]))?$column_array[$key]:'('.$key.')';
		$_table = $this->string2table($table);
		$current = $this->_getIndexKeys($table, true);
		$query = "ALTER TABLE ".$_table." ";
		$msg = array();

		switch($job) {
			case 'add':
				if(isset($current[$key])) {
					if($echo)
						echo '<div class="error fade"><p>'.sprintf(__('"%s" key already exists', 'slimstat-admin'), $key).'</p></div>';
					return true;
				}
				$msg['fail'] = sprintf(__('Failed to add "%s" index key.', 'slimstat-admin'), $key);
				$msg['success'] = sprintf(__('"%s" index key successfully added', 'slimstat-admin'), $key);
				$query .= "ADD {$index_type} ".$key." ".$columns." ";
			break;
			case 'remove':
				if(!isset($current[$key])) {
					if($echo)
						echo '<div class="error fade"><p>'.sprintf(__('"%s" key does not exists', 'slimstat-admin'), $key).'</p></div>';
					return true;
				}
				$msg['fail'] = sprintf(__('Failed to remove "%s" index key.', 'slimstat-admin'), $key);
				$msg['success'] = sprintf(__('"%s" index key successfully removed', 'slimstat-admin'), $key);
				$query .= "DROP INDEX ".$key." ";
			break;
			case 'readd':
				if(!isset($current[$key]))
					$query .= "ADD {$index_type} ".$key." ".$columns." ";
				else 
					$query .= "DROP INDEX ".$key.", ADD {$index_type} ".$key." ".$columns." ";
				$msg['fail'] = sprintf(__('Failed to re-add "%s" index key.', 'slimstat-admin'), $key);
				$msg['success'] = sprintf(__('"%s" index key successfully re-added', 'slimstat-admin'), $key);
			break;
			default:
				if($echo)
					echo '<div class="error fade"><p>'.__('job(add,remove,readd) is not defined', 'slimstat-admin').'</p></div>';
				return false;
			break;
		}
		$do_indexing = $wpdb->query($query);
		if($echo) {
			if($do_indexing === false) {
				echo '<div class="error fade"><p>'.$msg['fail'].'</p></div>';
			} else {
				echo '<div class="updated fade"><p>'.$msg['success'].'</p></div>';
			}
		} else {
			return ($do_indexing !== false);
		}
	}

	function OptimizeTables($tables=array('common')) {
		global $wpdb;
		if(!is_array($tables))
			$tables = (array)$tables;
		foreach($tables as $table) {
			if(false === $wpdb->query("OPTIMIZE TABLE ".$this->string2table($table)." "))
				return false;
		}
		return true;
	}

	function maybe_add_column($tablename, $column_name, $query='', $checkonly=false) {
		global $wpdb;
		if(!$checkonly) {
			foreach($wpdb->get_col("DESC $tablename", 0) as $column)
				if($column == $column_name)
					return true;
			$add_column = $wpdb->query($query);
		}
		foreach($wpdb->get_col("DESC $tablename", 0) as $column) 
			if($column == $column_name)
				return true;
		return false;
	}

	/*
	$check : array('Type'=>'int(11) unsigned', 'Null'=>'YES')
	*/
	function maybe_change_column($table, $column, $check=array(), $query='', $checkonly=false) {
		global $wpdb;
		$valid_check = (is_array($check) && !empty($check));
		if ( $checkonly && !$valid_check )
			return false;// we don't have any terms for check

		$checked = false;
		if (!$checkonly) {
			foreach ((array)$wpdb->get_results("DESCRIBE `$table`") as $col) {
				if ($col->Field != $column) continue;
				if ($valid_check) {
					foreach($check as $what => $val) {
						$checked = ( $col->{$what} && strtolower($val) == strtolower($col->{$what}) );
						if (!$checked) break;
					}
					if ($checked) // the column match your terms
						return true;
				}
				$update = $wpdb->query($query);
			}
		}
		foreach ((array)$wpdb->get_results("DESCRIBE `$table`") as $col) {
			if ($col->Field != $column) continue;
			if ($valid_check) {
				foreach($check as $what => $val) {
					$checked = ( $col->{$what} && strtolower($val) == strtolower($col->{$what}) );
					if (!$checked) break;
				}
				if ($checked) // the column match your terms
					return true;
			}
		}
		return false;
	}

	function maybe_remove_indexkey($key, $table, $index_type="INDEX") {
		$upgrade = true;
		$index = $this->_getIndexKeys($table, true);
		if(isset($index[$key]))
			$upgrade = $this->_do_indexing('remove', $key, $table, false, $index_type);
		return $upgrade;
	}

	function maybe_add_indexkey($key, $table, $index_type="INDEX") {
		$upgrade = true;
		$index = $this->_getIndexKeys($table, true);
		if(!isset($index[$key]))
			$upgrade = $this->_do_indexing('add', $key, $table, false, $index_type);
		return $upgrade;
	}

	function force_add_indexkey($key, $table, $index_type="INDEX") {
		$upgrade = true;
		$index = $this->_getIndexKeys($table, true);
		// common table
		$add = (isset($index[$key])) ? 'readd' : 'add';
		$upgrade = $this->_do_indexing($add, $key, $table, false, $index_type);
		return $upgrade;
	}

	function pack_indexkey($table, $val="DEFAULT") {
		global $wpdb;
		$_table = $this->string2table($table);
		if(false === $wpdb->query("ALTER TABLE {$_table} PACK_KEYS = {$val} "))
			return false;
		return true;
	}

	function _reset_slim_index() {
		global $wpdb;
		
		$myIndexStructure = $wpdb->get_results("SHOW INDEX FROM $this->old_stats", 'ARRAY_A');
		foreach ( $myIndexStructure as $index_details ) {
			if ( $index_details['Key_name'] == 'dt_smartidx' ) {
				$query = "ALTER TABLE ".$this->old_stats." DROP INDEX dt_smartidx ";
				if($wpdb->query($query) === false) { return false; break; }
				else { return true; break; }
			}
		}
		return true;
	}

	function _add_slim_dtindex() {
		global $wpdb;
		$reset_idx = $this->_reset_slim_index();
		if($reset_idx) {
			$query = "ALTER TABLE ".$this->old_stats." ADD INDEX dt_smartidx (dt,remote_ip) ";
			$add_index = $wpdb->query($query);
			if(!$add_index) {
				return false;
			}
			return true;
		}
		return false;
	}

	function _set_idindex() {
		global $wpdb;
		$idIndexStructure = $wpdb->get_results("SHOW INDEX FROM $this->table_shortstat", 'ARRAY_A');
		$id_index = false;
		if($idIndexStructure) {
			foreach ( $idIndexStructure as $index_details ) {
				if ( $index_details['Key_name'] == 'id' ) {
					$id_index = true;
				}
			}
		}
		if ($id_index == false) {
			$query = "ALTER TABLE ".$this->table_shortstat." ADD UNIQUE INDEX id (id) ";
			if($wpdb->query($query) === false) return false;
			return true;
		}
		return true;
	}
	
	function set_dt_type() {
		global $wpdb;
		$query = "SELECT dt_start, dt_end FROM ".$this->table_dt." WHERE type = 0 ";
		if($rs = $wpdb->get_results($query)) {
			foreach($rs as $r) {
				$interval = $r->dt_end - $r->dt_start;
				if ($interval > 3500 && $interval < 3700 ) $type = 1;//hour
				elseif ($interval > 8600 && $interval < 8700 ) $type = 2;//date
				elseif ($interval > 604000 && $interval < 605000 ) $type = 3;//week
				elseif ($interval > 2160000 && $interval < 2850000 ) $type = 4;//month
				else $type = 9;
				$query = "UPDATE ".$this->table_dt." SET type = '".$type."' 
							WHERE dt_start = '".$r->dt_start."'
								AND dt_end = '".$r->dt_end."' ";
				$wpdb->query($query);
			}
		}
	}
	
	function update_country_data($table = 'common') {
		global $wpdb;
		$_table = ($table == 'feed') ? $this->table_feed : $this->table_stats;

		$_where = "country NOT IN ('a1', 'a2', 'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'ap', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'ax', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bl', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'fx', 'ga', 'gb', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv', 'ly', 'ma', 'mc', 'md', 'me', 'mf', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'o1', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'rs', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'st', 'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'za', 'zm', 'zw')";

		$unknown_countries = $wpdb->get_results("SELECT remote_ip FROM $_table WHERE remote_ip <> 0 AND $_where GROUP BY remote_ip");
		if(!$unknown_countries)
			return true;
		foreach($unknown_countries as $empty) {
			$country = SSTrack::_determineCountry($empty->remote_ip);
			if('' != $country) {
				$update = $wpdb->query("UPDATE $_table SET country='{$country}' WHERE remote_ip='{$empty->remote_ip}' AND $_where ");
			} else 
				$update = true;
			if(!$update)
				return false;
		}
		return $update;
	}

	//set shortstat searchterms (set_ss_search, empty_none_search, determineInternalSearch)
	function set_ss_search() {
		global $wpdb;
		$query = "SELECT id, referer FROM ".$this->table_shortstat." WHERE searchterms = '' AND referer LIKE '%?%' ";
		if($results = $wpdb->get_results($query)) {
			foreach($results as $r) {
				$url = parse_url( $r->referer );
				$search = SSTrack::_determineSearchTerms($url);
				if($search == "") $search = "__NONE__";
				$wpdb->query("UPDATE ".$this->table_shortstat." SET searchterms = '".$search."' WHERE id = ".$r->id." ");
				print '. ';
			}
			echo "done";
		} else echo "no results";
	}

	function empty_none_search() {
		global $wpdb;
		$empty = $wpdb->query("UPDATE ".$this->table_shortstat." SET searchterms = '' WHERE searchterms LIKE '__NONE__' ");
		if($empty === false) 
			return false;
		return true;
	}

	function determineInternalSearch() {
		global $wpdb;
		$query = "SELECT id, resource FROM ".$this->table_shortstat." WHERE searchterms = '' AND resource LIKE '%s=%' ";
		if($results = $wpdb->get_results($query)) {
			foreach($results as $r) {
				$url = parse_url('http://willbe.removed.com'.$r->resource);
				if (isset($url['query'])) {
					parse_str($url['query'], $q);
					if(isset($q['s'])) {
						$search = urldecode($q['s']);
						$search = $this->convert_encoding( $q['s'] );
						$wpdb->query("UPDATE ".$this->table_shortstat." SET searchterms = '".$search."' WHERE id = ".$r->id." ");
						$wpdb->query("UPDATE ".$this->table_shortstat." SET resource = '0' WHERE id = ".$r->id." ");
						print '. ';
					}
				} else echo 'no-resource';
			}
			echo 'done!';
		} else echo 'no-results';
	}

	function importResourceData($step) {
		$substep = (!isset($_GET['substep_import_rs'])) ? 0 : (int)$_GET['substep_import_rs'];
		$upgrade = true;

		$message = array( 'first'=>__('Importing <em>resource data</em>', 'slimstat-admin'), 'fail'=>__('Failed to operate this sub-step(%s)', 'slimstat-admin'), 'ok'=>'<p>Ok, done.</p>'.__('Now we will do the next sub-step(%s)') );

		$buttontext = array('first'=>__('Start This Sub-Step', 'slimstat-admin'), 'fail'=>__('Do Over Again', 'slimstat-admin'), 'ok'=>__('Do This Step', 'slimstat-admin') );

		require_once(SLIMSTATPATH . 'lib/upgrade.php');
		switch($substep) {
			case 0:
				$done = true;
			break;
			case 1:
				$done = SSUpgrade::insertLocalSearchResource();
			break;
			case 2:
				$done = SSUpgrade::insertOldResources();
			break;
			case 3:
				$done = SSUpgrade::updateResourceData();
			break;
			case 4:
				$done = SSUpgrade::updatePostTitleData();
			break;
			case 5:
				$done = SSUpgrade::removeResource();
			break;
			case 6:
				$done = SSUpgrade::renameRS_ID('common');
			break;
			case 7:
				$done = SSUpgrade::renameRS_ID('feed');
			break;
			case 8:
				$done = SSUpgrade::addIndexToResource();
			break;
			case 9:
				$done = SSUpgrade::OptimizeTables(array('common', 'feed', 'resource'));
			break;
			case 10:
				return true;
			break;
		}
		$this->substep_form($done, $step, $substep, $message, $buttontext, array('slim2ex.php', 'import_rs'));
		return array(1);
	}

	function substep_form($done, $step, $substep, $_message, $_buttontext, $file=array('index.php', 'no_step')) {
		if($substep == 0) {
			$substep = 1;
			$message = $_message['first'];
			$buttontext = $_buttontext['first'];
		} elseif (!$done) {
			$message = sprintf($_message['fail'], $substep);
			$buttontext = $_buttontext['fail'];
		} else {
			$substep++;
			$message = sprintf($_message['ok'], $substep);
			$buttontext = $_buttontext['ok'];
		}
		$action = $file[0].'?step='.$step.'&amp;substep_'.$file[1].'='.$substep;
?>
<script type="text/javascript">
function disable_substep_submit(el) {
	el.disabled = 'true';
}
</script>
<h3><?php printf(__('Sub-Step %s', 'slimstat-admin'), $substep); ?></h3>
<form name="slimstatadmin_substep" method="post" action="<?php echo $action; ?>">
<ul><li><?php echo $message; ?></li></ul>
<p class="submit"><input type="submit" name="substep_submit" onclick="disable_substep_submit(this);" value="<?php echo $buttontext; ?>" /></p>
</form>
<?php
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSAdmin();
		}
		return $instance[0];
	}

}//end of class
endif;

$ssAdmin =& SSAdmin::get_instance();
?>
