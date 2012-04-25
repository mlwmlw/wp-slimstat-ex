<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

if (!class_exists('SSPins')) :
class SSPins {

	function SSPins() {
//		$this->_init();
	}

	// To make extra table for your pin,
	// set 'extra_table' to 1 on pin_actions()
	// define your table name and structure on 
	// var $extra_table = array('your_table_name' =>'table_structure');
	/* e.g.
	var $extra_table = array(
		'geo' => "`ip` INT(10) unsigned NOT NULL default '0',
			`country_abrv` CHAR(3) NOT NULL default '',
			`city` VARCHAR(40) NOT NULL default '',
			`latitude` FLOAT NOT NULL default '0',
			`longitude` FLOAT NOT NULL default '0',
			UNIQUE KEY `ip` (`ip`)"
	);
	*/

	function _init() {
		$actions = $this->pin_actions();
		if ($actions['fellow_links'])
			add_filter('', array(&$this, 'fellow_links'), 10, 2);
	}

	function pin_actions() {
		return array( 'options' => 0, 'extra_table' => 0, 'fellow_links' => 0 );
	}

	// copy, paste and un-comment lines.
	function &getPinID() {
		$name = get_class($this);
		$id =& $this->_getPinID($name);
		return $id;
	}

	// copy, paste and un-comment lines.
	function &getMoID($num) {
		$pinid =& $this->getPinID();
		$id = ($pinid *100) + 1 + $num;
		return $id;
	}

	// pin options :: use echo or print... not return.
	// set 'options' to 1 on pin_actions()
	function pin_options() {
?>
<div class="updated"><p><?php _e('There is no available option for this Pin', SLIMSTAT_DOMAIN); ?></p></div>
<?php
	}
	
	// set 'options' to 1 on pin_actions()
	function pin_update_options() {
	}

	// when pin is activated.... what to do? 
	function activate_action() {
	}

	// when pin is deactivated.... what to do?
	function deactivate_action() {
	}

	// All pins has it's own pin_compatible() function, 
	// If your pin is compatible on any version of slimstat,
	// Just return array('compatible'=>true);
	function pin_compatible() {
		global $SlimCfg;
		return array(
			'compatible'=>false, 
			// no html, just text message only.
			'message'=>sprintf(__('This pin does not work with WP-SlimStat-Ex v<em>%s</em>', SLIMSTAT_DOMAIN), $SlimCfg->version)
		);
	}

	// when 'extra_table' is 1 on pin_actions() and $PIN_NAME->extra_table is set.
	function maybe_create_extra_table($extra_table, $check_only=false) {
		global $SlimCfg;
		if (!is_array($extra_table) || empty($extra_table))
			return;
		require_once(SLIMSTATPATH . 'lib/setup.php');
		foreach($extra_table as $tname=>$structure) {
			$table_name = $SlimCfg->tbPrefix.$tname;
			$query = "CREATE TABLE {$table_name} ( {$structure} )";
			$create_table = SSSetup::maybe_create_table($table_name, $query, $check_only);
			if (!$create_table)
				return false;
		}
		return true;
	}

	function update_pin_options(&$pins) {
		if (!isset($_POST['slimstat_pin_options_submit']))
			return;
		if (!get_option('wp_slimstat_ex_pin_options')) {
			update_option('wp_slimstat_ex_pin_options', array());
		}
		foreach($pins as $pin) {
			$pin[0]->pin_update_options();
		}
		echo '<div class="updated fade"><p>'.__('Options saved.').'</p></div>';
	}

	function pin_option_menu_bar($pins) {
		if (!isset($pins[0][1]->id))
			return;
?>
<script type="text/javascript">//<![CDATA[
var current_pin_option;
function toggle_pin_otions(el, tid) {
	var panel = document.getElementById(tid);
	if (!current_pin_option)
		current_pin_option = document.getElementById('pin_option_<?php echo $pins[0][1]->id; ?>');
	if (!panel || current_pin_option == panel) 
		return;
	current_pin_option.style.display = 'none';
	panel.style.display = '';
	current_pin_option = panel;
}
//]]></script>
	<div id="pin_option_menu_bar"> |
<?php
		foreach($pins as $pin) {
?>
	<span><a href="javascript:void(0)" onclick="toggle_pin_otions(this, 'pin_option_<?php echo $pin[1]->id; ?>'); return false;"><?php echo $pin[1]->title; ?></a></span> |
<?php
		}
?>
	</div>
<?php
	}

	function get_option($name) {
		global $wp_sspin_options;
		if (!is_array($wp_sspin_options))
			$wp_sspin_options = array();

		if (isset($wp_sspin_options[$name]))
			return $wp_sspin_options[$name];
		$options = get_option('wp_slimstat_ex_pin_options');
		if (!$options) {
			$options = array();
			update_option('wp_slimstat_ex_pin_options', $options);
		}
		$wp_sspin_options = $options;		
		if (isset($options[$name]))
			return $options[$name];
		return false;
	}

	function update_option($name, $newval) {
		global $wp_sspin_options;
		if (!is_array($wp_sspin_options))
			$wp_sspin_options = array();

		$options = get_option('wp_slimstat_ex_pin_options');
		$oldval = $options[$name];
		if ($newval == $oldval)
			return;
		$options[$name] = $newval;
		$wp_sspin_options[$name] = $newval;
		update_option('wp_slimstat_ex_pin_options', $options);
	}

	function delete_option($name) {
		global $wp_sspin_options;
		$options = SSPins::get_option($name);
		if (isset($options[$name])) {
			unset($options[$name]);
			$wp_sspin_options = $options;
		}
		update_option('wp_slimstat_ex_pin_options', $options);
	}

	function fellow_links($preset, $id) {
		return $preset;
	}

	function getPin($id, $active=0) {
		global $wpdb, $SlimCfg;
		$and_active = $active ? " AND `active` = 1 " : "";
		$query = "SELECT * FROM {$SlimCfg->table_pins} WHERE `id` = {$id} {$and_active} LIMIT 1";
		return $wpdb->get_row($query);
	}

	function _getPinID($name) {
		global $wpdb, $SlimCfg;
		$query = "SELECT `id` FROM `".$SlimCfg->table_pins."` WHERE `name` = '".$name."' LIMIT 1 ";
		if ($row = $wpdb->get_row($query))
			return $row->id + 100;
		else return false;
	}
	
	function _getMoID($name, $num) {
		global $wpdb, $SlimCfg;
		$query = "SELECT `id` FROM `".$SlimCfg->table_pins."` WHERE `name` = '".$name."' LIMIT 1 ";
		if ($row = $wpdb->get_row($query)) $pinid = $row->id; else return 0;
		$id = ($pinid * 100) + 1 + $num;
		return $id;
	}

	function _getPins($active=0, $type=0, $force=false) {
		global $wpdb, $SlimCfg, $wp_sspins;
		if (!isset($wp_sspins))
			$wp_sspins = array();

		if (!is_numeric($type)) {
			$type = explode(',', $type);
			$type = array_splice($type, 2);
			$string2num = array('panel'=>0, 'func'=>1, 'both'=>2, 'all'=>5);
			$num = 0;
			foreach($type as $t) {
				$num += isset($string2num[$t]) ? isset($string2num[$t]) : 0;
			}
		}
		$active = (int)$active; // for older versions.
		$cache_key = md5($active.$type);
		if (!$force && isset($wp_sspins[$cache_key])) 
			return $wp_sspins[$cache_key];

		$is_active = ($active) ? "`active` = 1" : SLIMSTAT_DEFAULT_FILTER;
		// type:: 0 = panel, 1 = function, 2 = both, 3 = 0+2, 4 = 1+2, 5 = all pins
		switch($type) {
			case 0:case 1:case 2:
				$pin_type = " AND `type` = $type";
			break;
			case 3:
				$pin_type = " AND (`type` = 0 OR `type` = 2)";
			break;
			case 4:
				$pin_type = " AND (`type` = 1 OR `type` = 2)";
			break;
			case 5:
				$pin_type = "";
			break;
		}
		$q = "SELECT * FROM `".$SlimCfg->table_pins."` WHERE {$is_active}{$pin_type} ORDER BY title";
		if ($pins = $wpdb->get_results($q)) {
			$wp_sspins[$cache_key] = $pins;
		} else
			$wp_sspins[$cache_key] = array();
		return $wp_sspins[$cache_key];
	}

	function delete_pin($where_clause) {
		global $wpdb, $SlimCfg, $wp_sspins;
		if (!$wpdb->get_row("SELECT * FROM {$SlimCfg->table_pins} WHERE {$where_clause} LIMIT 1"))
			return;// not exists.
		$delete_pin = $wpdb->query("DELETE FROM {$SlimCfg->table_pins} WHERE {$where_clause} LIMIT 1");
		if (false === $delete_pin)// if failed to delete row, just deactivate pin.
			$wpdb->query("UPDATE {$SlimCfg->table_pins} SET `active` = 0 WHERE {$where_clause} LIMIT 1");
		$wp_sspins = array();
	}

	function findPins() {
		global $wpdb, $SlimCfg;
		if ( !is_dir(SLIMSTAT_PINPATH) || !is_readable(SLIMSTAT_PINPATH) )
			return;

		$pins_dh = opendir( SLIMSTAT_PINPATH );
		$myPins = SSPins::_getPins(0,5);
		// check deleted pins
		foreach ($myPins as $index => $current) {
			if ( !is_dir(SLIMSTAT_PINPATH.$current->name ) || !file_exists(SLIMSTAT_PINPATH.$current->name.'/pin.php') ) {
				SSPins::delete_pin("`name` = '{$current->name}' ");
				array_splice($myPins, $index, 1);
			}
		}
		// if all pins are deleted
/*		if ( empty($myPins) )
			return;*/
		$_myPins = array();
		foreach ($myPins as $key=>$myPin)
			$_myPins[$myPin->name] = $myPin;

		while ( ( $pin_dir = readdir($pins_dh) ) !== false ) {
			if ( $pin_dir{0} == '.' || !is_dir( SLIMSTAT_PINPATH.$pin_dir ) || !file_exists( SLIMSTAT_PINPATH.$pin_dir.'/pin.php' ) )
				continue;

			$Pinfo = array('title'=>'', 'author'=>'', 'url'=>'', 'text'=>'', 'version'=>'', 'type'=>0);
			$Moinfo = array();
			$q = '';
			ob_start();
			@include_once(SLIMSTAT_PINPATH.$pin_dir.'/pin.php');
			if ( !class_exists($pin_dir) ) {
				$get_pin = $wpdb->get_row("SELECT * FROM $SlimCfg->table_pins WHERE name = '{$pin_dir}' LIMIT 1");
				if ($get_pin && $get_pin->active == 1)
					$deactivate = $wpdb->query("UPDATE $SlimCfg->table_pins SET active = 0 WHERE name = '{$pin_dir}' LIMIT 1");
				ob_end_clean();
				continue;
			}
			$temp_pin = new $pin_dir();
			$Pinfo['name'] = $pin_dir;
			$Pinfo = array_merge($Pinfo, (array)$temp_pin->Pinfo);
			$Moinfo = array_merge($Moinfo, (array)$temp_pin->Moinfo);

			$Pinfo['title'] = (empty($Pinfo['title']))?$Pinfo['name']:$Pinfo['title'];
			foreach($Moinfo as $num=>$_info) {
				if (!isset($_info['name']) || !method_exists($temp_pin, $_info['name']))
					unset($Moinfo[$num]);
			}
			if (empty($Moinfo))// it's functionable Pin
				$Pinfo['type'] = 1;
			$Moinfo = $wpdb->escape(serialize($Moinfo));

			$Pinfo = array_map('trim', $Pinfo);
			$Pinfo['type'] = (int)$Pinfo['type'];
			$title = $url = $text = $version = $type = null;
			extract($Pinfo);
			$q = '';
			if (isset($_myPins[$Pinfo['name']])) {
				$myPin = $_myPins[$Pinfo['name']];
				$compatible = $temp_pin->pin_compatible();
				$deactivateit = $myPin->active == 1 && !$compatible['compatible'];
				$active = $deactivateit ? 0 : $myPin->active;
				if ($myPin->version != $Pinfo['version'] || $deactivateit) {
					$q = "UPDATE `".$SlimCfg->table_pins."` SET `author` = '{$author}',
						`title` = '{$title}',
						`url` = '{$url}',
						`text` = '{$text}',
						`modules` = '{$Moinfo}',
						`version` = '{$version}',
						`type` = {$type},
						`active` = {$active}
						WHERE `name` = '{$myPin->name}' ";
				}
			} else {
				$q = "INSERT INTO `{$SlimCfg->table_pins}` (`name`, `title`, `author`, `url`, `text`, `modules`, `version`, `active`, `type`) 
					VALUES ('{$name}', '{$title}', '{$author}', '{$url}', 
						'{$text}', '{$Moinfo}', '{$version}', 0, '{$type}') ";
			}
			if ($q != '')
				$do_query = $wpdb->query($q);
			ob_end_clean();
		}// end while
	}

	function PinMenulinks() {
		global $SlimCfg;
		$pins =& SSPins::_getPins(1, 3);
		$r = '';
		if (!empty($pins)) {
			foreach ($pins as $pin) {
				$name = $pin->name;
				$id = $pin->id + 100;
				$r .= ' | ';
/*				if ($SlimCfg->option['use_ajax']) {
					$r .= '<a id="slm'.$id.'" class="slm" href="#" onclick="SlimStat.panel('.$id.'); return false;"> ';
				} else {*/
					$r .= '<a id="slm'.$id.'" class="slm'.(($SlimCfg->get['pn'] == $id)?' slm_current':'').'" href="?page=wp-slimstat-ex&amp;panel='.$id.'"> ';
//				}
				$r .= ''.__($pin->title, SLIMSTAT_DOMAIN).'</a>'."\n";
			}
		return $r;
		}
	}

	function get_pin_nav() {
		global $SlimCfg;
		$pins =& SSPins::_getPins(1, 3);
		$items = array();
		if (!empty($pins)) {
			foreach ($pins as $pin) {
				$id = $pin->id + 100;
				$items[$id] = __($pin->title, SLIMSTAT_DOMAIN);
			}
		}
		return $items;
	}

	function _incPins($type = 0) {
		global $wpdb, $SlimCfg;
		$pins = SSPins::_getPins(1, $type);
		if (!is_array($pins) || empty($pins))
			return;
		foreach($pins as $pin) {
			$file = SLIMSTAT_PINPATH . $pin->name . '/pin.php';
			if (!file_exists($file))
				continue;
			include_once($file);
		}
	}

	function register_pin($pin) {
		global $SlimCfg;
		if (!is_array($SlimCfg->pinid2modlue))
			$SlimCfg->pinid2modlue = array();

		$pinid = $pin->id + 100;
		if (empty($pin->modules) || isset($SlimCfg->pinid2modlue[$pinid]))
			return;
		$modules = unserialize($pin->modules);
		$SlimCfg->pinid2modlue[$pinid]['name'] = $pin->name;
		foreach ($modules as $moid => $moinfo) {
			$moid = ($pin->id * 100) + 1 + $moid;
			$SlimCfg->pinid2modlue[$pinid][$moid] = $moinfo['name'];
		}
	}

	function _resetPins() {
		global $wpdb, $SlimCfg;
		$query = "TRUNCATE TABLE `".$SlimCfg->table_pins."` ";
		if ($wpdb->query($query) === false)
			return false;
		return true;
	}

	function current_filters(){
		global $SlimCfg;
		$use_ajax = $SlimCfg->option['use_ajax'];
		$output = '';
		if (!empty($SlimCfg->get['fi']) && isset($SlimCfg->get['ff'])) {
			switch($SlimCfg->get['ff']) {
				case 0:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Domain', SLIMSTAT_DOMAIN);
				break;
				case 1:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Search string', SLIMSTAT_DOMAIN);
				break;
				case 2:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Resource', SLIMSTAT_DOMAIN);
				break;
				case 3:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Remote IP', SLIMSTAT_DOMAIN);
				break;
				case 4:
				$fi_val = SSFunction::_translateBrowserID($SlimCfg->get['fi']);
				$fi_title = __('Browser', SLIMSTAT_DOMAIN);
				break;
				case 5:
				$fi_val = SSFunction::_translatePlatformID($SlimCfg->get['fi']);
				$fi_title = __('Platform', SLIMSTAT_DOMAIN);
				break;
				case 6:
				$fi_val =  __($SlimCfg->get['fi'], SLIMSTAT_DOMAIN);
				$fi_title = __('Country', SLIMSTAT_DOMAIN);
				break;
				case 7:
				$fi_val =  __($SlimCfg->get['fi'], SLIMSTAT_DOMAIN);
				$fi_title = __('Language', SLIMSTAT_DOMAIN);
				break;
				case 99:
				$fi_val =  __($SlimCfg->get['fi'], SLIMSTAT_DOMAIN);
				$fi_title = __('Custom', SLIMSTAT_DOMAIN);
				default:
				break;
			}
			$output .= "\t".'<p><span class="filter_string">';
			$output .= $fi_title.' : '.$fi_val.'</span>';
			$url = SSFunction::get_url(array('fi' => ''));
			$output .= ' <span class="reset-filter">{<a class="ajax-request-link" href="'.$url.'" id="reset-filters" title="'.__('Reset filters', SLIMSTAT_DOMAIN).'"> '.__('Reset', SLIMSTAT_DOMAIN).' </a>}</span></p>'."\n";
		}
/*		if (!empty($SlimCfg->get['fd'])) {
			$dt_start = date( __('d/m/Y H:i', SLIMSTAT_DOMAIN), $SlimCfg->time($SlimCfg->get['fd'][0]) );
			$dt_end = date( __('d/m/Y H:i', SLIMSTAT_DOMAIN), $SlimCfg->time($SlimCfg->get['fd'][1]) );
			$output .= "\t".'<br /><br /><span class="filter_string">';
			$output .= $dt_start.' - '.$dt_end.'</span>';
			$url = SSFunction::get_url(array('fd' => ''));
			$output .= ' [ <a class="ajax-request-link" href="'.$url.'" id="reset-interval" title="'.__('Reset interval', SLIMSTAT_DOMAIN).'">'.__('Reset interval', SLIMSTAT_DOMAIN).'</a> ]';
		}*/
		return $output;
	}

	function _filterIntervalLink() {
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
			$range_str = $SlimCfg->date('n/j/Y', $SlimCfg->time_switch($first_hit, 'blog'), false).' - '.$SlimCfg->date('n/j/Y', $SlimCfg->time(), false);
			$range_int = '';
			$interval_reset = '';
		}

		$_form .= '<input type="hidden" id="fd" name="fd" value="'.$range_int.'" />';
		$_form .= '<input type="text" size="28" id="fd_str" readonly="true" value="'.$range_str.'" />';
		$_form .= $interval_reset;

		$_form .= '<input type="hidden" name="action" value="request_panel" />';
		if ($SlimCfg->get['view_mode'])
			$_form .= '<input type="hidden" name="view_mode" value="'.$SlimCfg->get['view_mode'].'" />';
		$_form .= wp_nonce_field('slimstat-view-stats', '_wpnonce', false, false);

		$_form .= '</p>';

		$_form .= "\t".'</form>'."\n";

//		$pinid =& $this->getPinID();
//		$_form .= SSPins::current_filters($pinid);

		return $_form;
	}

	function __filterIntervalLink() {
		global $SlimCfg;
		$output = "";
		$class = 'fd-link';
		$filter_img = "<img src=\"".$SlimCfg->pluginURL."/css/filter-self.gif\" alt=\"Filter\" style=\"vertical-align:bottom;\" />";
		$pinid =& $this->getPinID();
		$use_ajax = $SlimCfg->option['use_ajax'];
		$output .= "<br />\n";
		$output .= "\t<div class=\"interval-filter\">&nbsp;&nbsp;<span>".__('Time interval', SLIMSTAT_DOMAIN)." : \n";
		// today
		$dt_end = $SlimCfg->midnight_db + 86399;// 7 days
		$fd_encode = urlencode($SlimCfg->midnight_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;Today&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('Today', SLIMSTAT_DOMAIN).$filter_img."</a> | ";
		// yesterday
		$dt_start_db = $SlimCfg->midnight_db - 86400;
		$dt_end = $SlimCfg->midnight_db - 1;
		$fd_encode = urlencode($dt_start_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;Yesterday&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('Yesterday', SLIMSTAT_DOMAIN).$filter_img."</a> | ";
		// this week
		$dt_start = $SlimCfg->midnight_print;
		$dt_end = $SlimCfg->midnight_db + 86399;
		while ( $SlimCfg->date( "w", $dt_start, false ) !=  1 ) { // move back to start of this week (1:Monday, 0:Sunday)
			$dt_start -= 86400;
		}
		$dt_start_db = $SlimCfg->time_switch($dt_start, 'db'); // GMT time
		if ($dt_end - $dt_start_db <= 0 ) $dt_start_db = $SlimCfg->midnight_db;
		$fd_encode = urlencode($dt_start_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;This week&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('This week', SLIMSTAT_DOMAIN).$filter_img."</a> | ";
		// last week
		$dt_end = $dt_start_db - 1;
		$dt_start_db = ($dt_start_db - 604800);
		$fd_encode = urlencode($dt_start_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;Last week&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('Last week', SLIMSTAT_DOMAIN).$filter_img."</a> | ";
		// this month
		$dt_start = $SlimCfg->midnight_print;
		$dt_end = ($SlimCfg->midnight_db + 86399);
		while ( $SlimCfg->date( "j", $dt_start, false ) > 1 ) { // Move back to start of this month
			$dt_start -= 86400;
		}
		$dt_start_db = $SlimCfg->time_switch($dt_start, 'db'); // back to server time
		$fd_encode = urlencode($dt_start_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;This month&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('This month', SLIMSTAT_DOMAIN).$filter_img."</a> | ";
		// last month
		$dt_end = $dt_start_db - 1;
		$dt_start_db = $SlimCfg->mktime( array('h'=>0, 'i'=>0, 's'=>0, 'm'=>'-1', 'd'=>1), $dt_start, 'db');
		$fd_encode = urlencode($dt_start_db.'|'.$dt_end);
		$href = SSFunction::get_url(array('panel'=>$pinid, 'fd'=>$fd_encode));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View stats for &#039;Last month&#039;', SLIMSTAT_DOMAIN)."\">";
		$output .= __('Last month', SLIMSTAT_DOMAIN).$filter_img."</a>";

		$output .= SSPins::current_filters($pinid);		

		$output .= "</span></div>\n";
		return $output;
	}

}// end of class
endif;
?>