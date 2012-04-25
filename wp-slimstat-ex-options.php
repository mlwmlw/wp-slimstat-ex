<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

class wp_slimstat_ex_options {
	var $page, $admin_url;
	var $tmp_site = array();

	function wp_slimstat_ex_options() {
		global $SlimCfg;
		$this->page = trim($_GET['page']);
		$this->admin_url = get_option('siteurl') . '/wp-content/plugins/' . $SlimCfg->basedir . '/lib/ss-admin';
	}

	function get_url($page='option', $noheader=false) {
		global $SlimCfg;
		switch ($page) {
			default:
				return $SlimCfg->option_page . ($noheader ? '&amp;noheader=true':'');
			break;
			case 'option': case 'pin': case 'exclusion': case 'permission': case 'site': case 'admin':
				return 'admin.php?page=wp-slimstat-ex-' . $page . ($noheader ? '&amp;noheader=true':'');
			break;
		}
	}

	function get_message($id=0) {
		$class = 'updated';
		switch($id):
		case 1:
			$msg = __('Wp-SlimStat-Ex options updated', SLIMSTAT_DOMAIN);
		break;
		case 2:
			$msg = __('You do not have sufficient permissions to access this page.', SLIMSTAT_DOMAIN);
			$class = 'error';
		break;
		case 3:
		break;
		case 4:
		break;
		case 5:
		break;
		default:
			$msg = '';
		break;
		endswitch;
		if ('' != $msg)
			return "<div class='$class'><p>$msg</p></div>";
		return $msg;
	}

	function update_options() {
		global $SlimCfg;
		if ( !isset($_POST['ssex_op']) || !is_array($_POST['ssex_op']) )// Main Options
			return;

		if (!$SlimCfg->has_cap('manage_slimstat_options')) {
			wp_redirect($this->get_url('option') . '&message=2');
			exit;
		}
		$is_int = array( 'tracking', 'usepins', 'guesstitle', 'cachelimit', 'limitrows', 'dbmaxage', 'iptohost', 'whois', 'meta', 'time_offset', 'use_ajax', 'ajax_history', 'nice_titles' );
		foreach($_POST['ssex_op'] as $key=>$value) {
			$SlimCfg->option[$key] = (in_array($key, $is_int)) ? (int)$value : stripslashes(trim($value));
		}
		update_option('wp_slimstat_ex', $SlimCfg->option);
		wp_redirect($this->get_url('option') . '&message=1');
		exit;
	}

	function options_page() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('manage_slimstat_options')) {
			echo '<div class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$this->update_options();
		if ($message = $_GET['message'])
			echo $this->get_message($message);
?>
<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('options-general'); ?>
	<h2><?php _e('SlimStat Options', SLIMSTAT_DOMAIN); ?></h2>
	<form name="slimstat_option" method="post" action="<?php echo $this->get_url('option', true); ?>"> 
	<div class="options">
<!-- General Options Start -->
	<h3><?php _e('General Options', SLIMSTAT_DOMAIN); ?></h3>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Enable Tracking?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[tracking]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['tracking']); ?>>enable</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['tracking']); ?>>disable</option>
		</select>
		<br />
<?php _e('&mdash; If you want to track blog visitors select &quot;enable&quot;', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Track Mode:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[track_mode]">
			<option value="full"<?php selected('full', $SlimCfg->option['track_mode']); ?>><?php _e('Entire blog', SLIMSTAT_DOMAIN); ?></option>
			<option value="footer"<?php selected('footer', $SlimCfg->option['track_mode']); ?>><?php _e('Blog pages only', SLIMSTAT_DOMAIN); ?></option>
			<option value="footer_feed"<?php selected('footer_feed', $SlimCfg->option['track_mode']); ?>><?php _e('Blog pages and feed', SLIMSTAT_DOMAIN); ?></option>
		</select>
		<br />
<?php _e('&mdash; If you need stats for REAL visitors, \'Blog pages only\' would be the one.', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Use Pins?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[usepins]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['usepins']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['usepins']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; If you want to use Pins select &quot;true&quot;', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Use AJAX?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[use_ajax]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['use_ajax']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['use_ajax']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; Use &quot;AJAX&quot; or not?. Setting it to false will disable some modules.', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
<?php if ($SlimCfg->option['use_ajax']) { ?>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php echo __('AJAX cache limit:', SLIMSTAT_DOMAIN) ?></th> 
		<td><input name="ssex_op[cachelimit]" type="text" value="<?php echo $SlimCfg->option['cachelimit']; ?>" size="3" /> 
		  <?php echo __('minutes', SLIMSTAT_DOMAIN); ?><br />
<?php echo __('&mdash; Cache time of Ajax result page by minutes. (disable cache = 0)', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Save AJAX History:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[ajax_history]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['ajax_history']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['ajax_history']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; Save AJAX browsing history or not?. If &quot;true&quot;, your web-browser get forward/back button working with AJAX.', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
<?php } ?>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('SQL limit rows:', SLIMSTAT_DOMAIN) ?></th> 
		<td><input name="ssex_op[limitrows]" type="text" value="<?php echo $SlimCfg->option['limitrows']; ?>" size="3" /> 
		<?php _e('rows', SLIMSTAT_DOMAIN); ?>
		<br />
<?php _e('&mdash; Limit rows of each modules', SLIMSTAT_DOMAIN) ?>
		</td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('DB max-age:', SLIMSTAT_DOMAIN) ?></th> 
		<td><input name="ssex_op[dbmaxage]" type="text" value="<?php echo $SlimCfg->option['dbmaxage']; ?>" size="3" /> 
		<?php _e('days', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php _e('&mdash; Set database max-age by days (disable reduce DB : 0)', SLIMSTAT_DOMAIN) ?>
		<br />
		<?php _e('&mdash; You can reduce DB from ', SLIMSTAT_DOMAIN) ?>"<a href="<?php echo $this->admin_url; ?>/admin.php">ss-admin</a>"<?php _e(' page', SLIMSTAT_DOMAIN) ?>
		</td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('IPTC method:', SLIMSTAT_DOMAIN) ?></th> 
		<td><ul>
<?php if (true === SLIMSTAT_EXTERNAL_IPTC) { ?>
			<li><?php _e('You are using external IPTC remote DB', SLIMSTAT_DOMAIN); ?></li>
<?php } else {
			$geo_file = $SlimCfg->geoip == 'city' ? 'GeoLiteCity.dat' : 'GeoIP.dat';
			$geo_url = 'http://www.maxmind.com/app/' . ($SlimCfg->geoip == 'city' ? 'geolitecity' : 'geoip_country');
?>
			<li><?php printf(__('You are using GeoIP databse(%s)', SLIMSTAT_DOMAIN), $geo_file); ?></li>
			<?php if ($SlimCfg->geoip == 'country') { ?>
			<li><?php _e('As for the GeoSlimStat Pin, using <a href="http://www.maxmind.com/app/geolitecity">GeoLite City</a> is about 100 times faster than remote query.', SLIMSTAT_DOMAIN); ?></li>
			<?php } ?>
			<li><?php printf(__('You can update your database file every start of month from <a href="http://www.maxmind.com">MaxMind</a>\'s free <a href="%s">GeoIP Source</a> page.', SLIMSTAT_DOMAIN), $geo_url); ?></li>
<?php } ?>
		</ul>
<?php _e('&mdash; IP to Country Resource', SLIMSTAT_DOMAIN) ?>
		</td> 
	</tr>
	</table>
	<p class="submit">
	<input type="submit" name="update_options" value="<?php _e('Update Options', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
<!-- General Options End -->
	</div>
	<div class="options">
<!-- Display Options Start -->
	<h3><?php _e('Display Options', SLIMSTAT_DOMAIN); ?></h3>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('View mode:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[view_mode]">
			<option value="table"<?php selected('table', $SlimCfg->option['view_mode']); ?>><?php _e('Table', SLIMSTAT_DOMAIN); ?></option>
			<option value="chart"<?php selected('chart', $SlimCfg->option['view_mode']); ?>><?php _e('Chart', SLIMSTAT_DOMAIN); ?></option>
		</select>
		<br />
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Visit type:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[visit_type]">
			<option value="uniques"<?php selected('uniques', $SlimCfg->option['visit_type']); ?>>uniques</option>
			<option value="visits"<?php selected('visits', $SlimCfg->option['visit_type']); ?>>visits</option>
		</select>
		<br />
<?php _e('&mdash; Select visit type. uniques: count unique ip, visits: 30-minute intervals', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Guess post title?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[guesstitle]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['guesstitle']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['guesstitle']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; Get post title from resource(page address)', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Get host name?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[iptohost]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['iptohost']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['iptohost']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; Get host name from remote address', SLIMSTAT_DOMAIN) ?>(IP)</td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Use Whois link?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[whois]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['whois']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['whois']); ?>>false</option>
		</select> &mdash; 
		<select name="ssex_op[whois_db]">
			<option value="dnsstuff"<?php selected('dnsstuff', $SlimCfg->option['whois_db']); ?>>dnsstuff.com</option>
			<option value="iplookup"<?php selected('iplookup', $SlimCfg->option['whois_db']); ?>>ip-lookup.net</option>
		</select>
		<br />
<?php _e('&mdash; Use &quot;Whois&quot; link on &quot;Visitors&quot; modules', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
	</table>
	<p class="submit">
	<input type="submit" name="update_options" value="<?php _e('Update Options', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
<!-- Display Options End -->
	</div>
	<div class="options">
<!-- Extra Options Start -->
	<h3><?php _e('Extra Options', SLIMSTAT_DOMAIN); ?></h3>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Nice Titles:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ssex_op[nice_titles]">
			<option value="1"<?php selected(1, (int)$SlimCfg->option['nice_titles']); ?>>true</option>
			<option value="0"<?php selected(0, (int)$SlimCfg->option['nice_titles']); ?>>false</option>
		</select>
		<br />
<?php _e('&mdash; Enable or disable &quot;Nice Titles&quot;. Powered by <a href="http://www.dustindiaz.com/sweet-titles-finalized">SweetTitles</a>', SLIMSTAT_DOMAIN) ?></td> 
	</tr>
<?php /* ?>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('Your server time is:', SLIMSTAT_DOMAIN) ?></th> 
		<td><?php echo date('Y-m-d g:i:s a', time()); ?></td> 
	</tr>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('Time Offset:', SLIMSTAT_DOMAIN) ?></th> 
		<td><input name="ssex_op[time_offset]" type="text" value="<?php echo $SlimCfg->option['time_offset']; ?>" size="3" /> 
		  <?php _e('hours', SLIMSTAT_DOMAIN); ?>
<?php _e('&mdash; Time offset from server time by hours.(NOT gmt offset)', SLIMSTAT_DOMAIN) ?>
		</td> 
	</tr>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('Your blog time is:', SLIMSTAT_DOMAIN) ?></th> 
		<td><?php echo date('Y-m-d g:i:s a', (time() + ($SlimCfg->option['time_offset'] * 60 * 60))); ?></td> 
	</tr>
<?php */ ?>
	</table>
	<p class="submit">
	<input type="submit" name="update_options" value="<?php _e('Update Options', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
<!-- Extra Options End -->
	</div>
	</form>
</div><!-- wrap -->
<?php 
	}

	function activate_pin($id, &$pin) {
		global $SlimCfg, $wpdb;
		$location = $this->get_url('pin');
		$pin_file = SLIMSTAT_PINPATH . $pin->name . '/pin.php';
		if ($pin->active) {
			wp_redirect($location . '&activate=true&msg_code=already_act');
			exit;
		}
		ob_start();
		include_once($pin_file);
		if (!class_exists($pin->name)) {
			ob_end_clean();
			wp_redirect($location . '&activate=false&msg_code=not_compat');
			exit;
		}

		$temp_pin = new $pin->name();
		if ('sspins' != strtolower(get_parent_class($temp_pin))) {
			ob_end_clean();
			wp_redirect($location . '&activate=false&msg_code=not_compat');
			exit;
		}

		$compatible = $temp_pin->pin_compatible();
		if (!$compatible['compatible']) {
			ob_end_clean();
			wp_redirect($location . '&activate=false&msg_code=not_compat');
			exit;
		}

		$actions = $temp_pin->pin_actions();
		if ( $actions['extra_table'] && isset($temp_pin->extra_table) && 
				is_array($temp_pin->extra_table) && !empty($temp_pin->extra_table) ) {// dobule check if Pin really needs extra table
			$create_table = SSPins::maybe_create_extra_table($temp_pin->extra_table);
			if (!$create_table) {
				ob_end_clean();
				wp_redirect($location . '&activate=false');
				exit;
			}
		}
		$do_action = $temp_pin->activate_action();
		ob_end_clean();
		$query = "UPDATE	`{$SlimCfg->table_pins}` SET `active` = 1 WHERE id = {$id} LIMIT 1";
		if ( false === $wpdb->query($query) )
			wp_redirect($location . '&activate=false');
		else 
			wp_redirect($location . '&activate=true');
		exit;
	}

	function deactivate_pin($id, &$pin) {
		global $SlimCfg, $wpdb;
		$location = $this->get_url('pin');
		$pin_file = SLIMSTAT_PINPATH . $pin->name . '/pin.php';
		if (!$pin->active) {
			wp_redirect($location . '&deactivate=true&msg_code=already_deact');
			exit;
		}
		ob_start();
		@include_once($pin_file);
		if (class_exists($pin->name)) {
			$temp_pin = new $pin->name();
			if ( 'sspins' == strtolower(get_parent_class($temp_pin)) )
				$temp_pin->deactivate_action();
		}
		ob_end_clean();
		$query = "UPDATE	`{$SlimCfg->table_pins}` SET `active` = 0 WHERE id = {$id} LIMIT 1";
		if ( false === $wpdb->query($query) )
			wp_redirect($location . '&deactivate=false');
		else 
			wp_redirect($location . '&deactivate=true');
		exit;
	}

	function manage_pins() {
		global $SlimCfg, $wpdb;
		if ( !isset($_GET["pinact"]) || ($_GET["pinact"] !== '0' && empty($_GET["pinact"])) )
			return;
		$pin_id = (int)$_GET['pinid'];
		$pin_active = (int)$_GET['pinact'];
		$action = $pin_active ? 'activate' : 'deactivate';
		$location = $this->get_url('pin');
		// check referer
		check_admin_referer("{$action}-pin_{$pin_id}");

		$pin = SSPins::getPin($pin_id);
		if (!$pin) {
			wp_redirect($location . '&' . $action . '=false&msg_code=invalid_id');
			exit;
		}
		$pin_file = SLIMSTAT_PINPATH . $pin->name . '/pin.php';
		if (!file_exists($pin_file)) {
			SSPins::delete_pin("`id` = $pin->id");
			wp_redirect($location . '&' . $action . '=false&msg_code=no_file');
			exit;
		}
		if ($pin_active) {
			$this->activate_pin($pin_id, &$pin);
		} else {
			$this->deactivate_pin($pin_id, &$pin);
		}
	}

	function option_pins() {
		global $SlimCfg;
		// activate or deactivate Pins
		$this->manage_pins();
		$default_compatible = SSPins::pin_compatible();
		$code_to_msg = array(
			'invalid_id' => __('Invalid Pin ID.', SLIMSTAT_DOMAIN),
			'no_file' => __('Pin file does not exists.', SLIMSTAT_DOMAIN),
			'already_act' => __('Pin already activated.', SLIMSTAT_DOMAIN),
			'already_deact' => __('Pin already deactivated.', SLIMSTAT_DOMAIN),
			'not_compat' => $default_compatible['message'],
		);
		$msg = '';
		if ( isset($_GET['activate']) && '' != $_GET['activate'] ) {
			$msg_class = $_GET['activate'] == 'true' ? 'updated' : 'error';
			if ( isset($_GET['msg_code']) && isset($code_to_msg[$_GET['msg_code']]) )
				$msg = $code_to_msg[$_GET['msg_code']];
			else 
				$msg = $_GET['activate'] == 'true' ? __('Pin activated', SLIMSTAT_DOMAIN) : __('Failed to activate Pin', SLIMSTAT_DOMAIN);
		} else if ( isset($_GET['deactivate']) && '' != $_GET['deactivate'] ) {
			$msg_class = $_GET['deactivate'] == 'true' ? 'updated' : 'error';
			if ( isset($_GET['msg_code']) && isset($code_to_msg[$_GET['msg_code']]) )
				$msg = $code_to_msg[$_GET['msg_code']];
			else 
				$msg = $_GET['deactivate'] == 'true' ? __('Pin deactivated', SLIMSTAT_DOMAIN) : __('Failed to deactivate Pin', SLIMSTAT_DOMAIN);
		}
		if ( '' != $msg ) {
			echo '<div class="'.$msg_class.' fade"><p>'.$msg.'</p></div>';
		}

//		require_once(SLIMSTATPATH . 'lib/pins.php');
		SSPins::findPins();
		$pins = SSPins::_getPins(0, 5, true);// get all pins
		$pageurl = $this->get_url('pin');
?>
<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('plugins'); ?>
  <h2><?php _e('Manage Pins', SLIMSTAT_DOMAIN) ?></h2>
	<div class="options">
	<h3><?php _e('Pins', SLIMSTAT_DOMAIN); ?></h3>
<?php 
		if ( empty($pins) ) {
			echo '<div class="updated"><p>There is no Pins available</p></div>'; 
		} else { 
?>
	<table width="100%" cellpadding="3" cellspacing="3" class="widefat">
		<thead>
	<tr>
		<th class="vers"><?php _e('ID', SLIMSTAT_DOMAIN); ?></th>
		<th class="name"><?php _e('Name (version)', SLIMSTAT_DOMAIN); ?></th>
		<th class="vers"><?php _e('Author', SLIMSTAT_DOMAIN); ?></th>
		<th class="desc"><?php _e('Description', SLIMSTAT_DOMAIN); ?></th>
		<th class="togl"><?php _e('Acitve', SLIMSTAT_DOMAIN); ?></th>
	</tr>
	</thead>
	<tbody id="plugins">
<?php 
			$alt = '';
			foreach($pins as $pin) {
				$alt = $alt == ' alternate' ? '' : ' alternate';
				$class_tr = $pin->active == 1 ? 'active' : 'waitingpin';
				$class_act = $pin->active == 1 ? 'delete' : 'edit';
				$act_text = $pin->active == 1 ? __('Deactivate', SLIMSTAT_DOMAIN) : __('Activate', SLIMSTAT_DOMAIN);
				$action = $pin->active == 1 ? 'deactivate' : 'activate';
				$act_link = wp_nonce_url($pageurl . "&amp;noheader=true&amp;pinact=".(int)!$pin->active."&amp;pinid={$pin->id}", "{$action}-pin_{$pin->id}");
?>
	<tr class="<?php echo $class_tr . $alt; ?>">
		<td class="vers"><?php echo $pin->id; ?></td>
		<td class="name"><?php echo $pin->title . " ( " . $pin->version . " )"; ?></td>
		<td class="vers"><a href="<?php echo $pin->url; ?>" title="Author URL"><?php echo $pin->author; ?></a></td>
		<td class="desc" width="50%"><?php echo $pin->text; ?></td>
		<td class="togl action-links"><a class="<?php echo $class_act; ?>" href="<?php echo $act_link; ?>"><?php echo $act_text; ?></a></td>
	</tr>
<?php 
			} /* foreach */
?>
	</tbody>
	</table>
	</div>
<?php 
			$active_pins = SSPins::_getPins(1,5);// get all active pins
			if (empty($active_pins))
				return;
//			$include_pins = SSPins::_incPins(0);// include all active pins - funtionable pins are already included.
			$i = 0;
			$_opt_pins = array();
			foreach($active_pins as $pin) {
				if (!class_exists($pin->name))
					continue;
				$_pin{$i} = new $pin->name();
				if (!is_a($_pin{$i}, 'SSPins'))
					continue;
				$_pin_opt = $_pin{$i}->pin_actions();
				if (!$_pin_opt['options'])
					continue;
				$_opt_pins[] = array($_pin{$i}, $pin);
				$i++;
			}
			if (empty($_opt_pins))
				return;
?>
	<h3><?php _e('Pin Options', SLIMSTAT_DOMAIN); ?></h3>
<?php 
			SSPins::update_pin_options($_opt_pins);
			SSPins::pin_option_menu_bar($_opt_pins);
?>
	<form name="slimstat_pin_options" id="slimstat_pin_options" method="post" action="<?php echo $this->get_url('pin'); ?>#slimstat_pin_options">
<?php
			$first_option = true;
			foreach($_opt_pins as $pin) {
				$display = $first_option ? '' : ' style="display:none;"';
				$first_option = false;
?>
	<div class="options" id="pin_option_<?php echo $pin[1]->id; ?>"<?php echo $display; ?>>
	<h4>[ <?php printf(__('%s Options', SLIMSTAT_DOMAIN), wp_specialchars($pin[1]->title, 1)); ?> ]</h4>
	<?php $pin[0]->pin_options(); ?>
	</div>
<?php
			}
?>
	<p class="submit">
	<input type="submit" name="slimstat_pin_options_submit" value="<?php _e('Update Options  &raquo;') ?>" />
	</p>
	</form>
</div><!-- wrap -->
<?php
		} /* is there any pins? */
	}

	function manage_exclusions() {
		global $SlimCfg;
		if ( isset($_POST['ig_op']) && is_array($_POST['ig_op']) ) {
			$checkboxes = array('ig_bots', 'ig_feeds', 'ig_validators', 'ig_tools');
			foreach($checkboxes as $box)
				$SlimCfg->exclude[$box] = isset($_POST['ig_op'][$box]);
			$intvals = array('ignore_bots');
			foreach($_POST['ig_op'] as $key=>$value) {
				if (!in_array($key, $checkboxes))
					$SlimCfg->exclude[$key] = (in_array($key, $intvals))?(int)$value:stripslashes(trim($value));
			}
			update_option('wp_slimstat_ex_exclude', $SlimCfg->exclude);
			echo '<div class="updated fade"><p>'.__('Wp-SlimStat-Ex options updated', SLIMSTAT_DOMAIN).'</p></div>';
		}
	}

	function option_exclusions() {
		global $SlimCfg;
		$this->manage_exclusions();
		$pageurl = $this->get_url('exclusion');
?>
<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('options-general'); ?>
  <h2><?php _e('Manage Exclusions', SLIMSTAT_DOMAIN) ?></h2>
	<form name="slimstat_option_exclusion" method="post" action="<?php echo $pageurl; ?>"> 
	<div class="options">
<!-- Exclusion Options Start -->
	<h3><?php _e('Exclusion Options', SLIMSTAT_DOMAIN); ?></h3>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('Ignore IP-List:', SLIMSTAT_DOMAIN) ?></th> 
		<td><?php _e('This setting define which remote ip will <em>always</em> not to be tracked.', SLIMSTAT_DOMAIN); ?><br />
		<?php _e('Seperate multiple ip with semi-colon( ; )', SLIMSTAT_DOMAIN); ?>
		<br />
		<textarea name="ig_op[ignore_ip]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['ignore_ip'],true); ?></textarea>
		</td> 
	</tr>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Ignore Bots?:', SLIMSTAT_DOMAIN) ?></th> 
		<td><select name="ig_op[ignore_bots]">
			<option value="0"<?php selected(0, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('No', SLIMSTAT_DOMAIN) ?></option>
			<option value="1"<?php selected(1, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Disable track', SLIMSTAT_DOMAIN) ?></option>
			<?php /* ?>
			<option value="2"<?php selected(2, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Disable display', SLIMSTAT_DOMAIN) ?></option>
			<option value="3"<?php selected(3, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Track and display', SLIMSTAT_DOMAIN) ?></option>
			<?php */ ?>
		</select>
		<br />
		<?php _e('&mdash; Ignore miscellaneous bots, crawlers and empty user-agent visitors.', SLIMSTAT_DOMAIN) ?><br />
		<!-- <?php //_e('&mdash; Selecting option related to "display" may slow down your stats page.', SLIMSTAT_DOMAIN) ?><br /> -->
<?php
		if ($SlimCfg->exclude['ignore_bots']) _e('&mdash; See more settings below.', SLIMSTAT_DOMAIN);
		else _e('&mdash; More options will shown while you enable this option.', SLIMSTAT_DOMAIN); 
?>
		</td>
	</tr>
<?php if ($SlimCfg->exclude['ignore_bots']) { ?>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('More Exclusions:', SLIMSTAT_DOMAIN) ?></th> 
		<td>
		<label for="ig_bots"><?php _e('Ignore famous Bots(google,yahoo,msn...)', SLIMSTAT_DOMAIN) ?> : </label>
		<input id="ig_bots" type="checkbox" name="ig_op[ig_bots]" value="1"<?php checked(1, $SlimCfg->exclude['ig_bots']); ?> />
		<br />
		<label for="ig_feeds"><?php _e('Ignore RPC Service(technorati,feedburner...) and RSS readers', SLIMSTAT_DOMAIN) ?> :</label>
		<input id="ig_feeds" type="checkbox" name="ig_op[ig_feeds]" value="1"<?php checked(1, $SlimCfg->exclude['ig_feeds']); ?> />
		<br />
		<label for="ig_validators"><?php _e('Ignore validators(w3c,feedvalidator...)', SLIMSTAT_DOMAIN) ?> : </label>
		<input id="ig_validators" type="checkbox" name="ig_op[ig_validators]" value="1"<?php checked(1, $SlimCfg->exclude['ig_validators']); ?> />
		<br />
		<label for="ig_tools"><?php _e('Ignore fetching tools (curl,snoopy...)', SLIMSTAT_DOMAIN) ?> : </label>
		<input id="ig_tools" type="checkbox" name="ig_op[ig_tools]" value="1"<?php checked(1, $SlimCfg->exclude['ig_tools']); ?> />
		</td>
	</tr>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('Black User-Agent List:', SLIMSTAT_DOMAIN) ?></th> 
		<td><?php _e('SlimStat will <em>always ignore</em> User-Agent below.', SLIMSTAT_DOMAIN); ?>
		<?php _e('Seperate multiple pattern with new line.', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php _e('Some <a href="http://php.net/manual/reference.pcre.pattern.syntax.php" title="syntax help">syntaxes</a>(^, $, *) are available.', SLIMSTAT_DOMAIN) ?> &mdash; <?php _e('"*" means non-whitespace characters (\\S*?)', SLIMSTAT_DOMAIN); ?>
		<br />
		<textarea name="ig_op[black_ua]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['black_ua'],true); ?></textarea>
		</td> 
	</tr>
	<tr valign="top"> 
		<th width="25%" scope="row"><?php _e('White User-Agent List:', SLIMSTAT_DOMAIN) ?></th> 
		<td><?php _e('SlimStat will <em>always track</em> User-Agent below. (will be applied before ignore list)', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php _e('Seperate multiple pattern with new line.', SLIMSTAT_DOMAIN); ?>
		<br />
		<textarea name="ig_op[white_ua]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['white_ua'],true); ?></textarea>
		</td>
	</tr>
<?php } ?>
	</table>
	<p class="submit">
	<input type="submit" name="update_options" value="<?php _e('Update Options', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
<!-- Exclusion Options End -->
	</div>
	</form>
</div><!-- wrap -->
<?php
	}

	function manage_permissions() {
		global $SlimCfg, $wp_roles;
		if (!$SlimCfg->has_cap('manage_options'))
			return;
		if ( isset($_POST['ssex_perm']) && is_array($_POST['ssex_perm']) ) {
			$new = $_POST['ssex_perm'];
			$roles = array_keys($wp_roles->role_names);
			if (empty($roles)) {
				echo '<div class="error fade"><p>'.__('No Roles.', SLIMSTAT_DOMAIN).'</p></div>';
				return;
			}
			foreach ($roles as $role) {
				if ( !isset($new[$role]) )
					$new[$role] = array();
			}
			if ($SlimCfg->caps != $new) {// if option has changed
				$SlimCfg->caps = array_merge($SlimCfg->caps, $new);
				update_option('wp_slimstat_ex_caps', $SlimCfg->caps);
				$SlimCfg->check_caps(true);// force update role caps
			}
			echo '<div class="updated fade"><p>'.__('Permissions updated.', SLIMSTAT_DOMAIN).'</p></div>';
		}
	}

	function option_permissions() {
		global $SlimCfg, $wp_roles;
		if (!$SlimCfg->has_cap('manage_options')) {
			echo '<div class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$this->manage_permissions();
?>
<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('users'); ?>
  <h2><?php _e('Manage Permissions', SLIMSTAT_DOMAIN) ?></h2>
	<form name="slimstat_option_permission" method="post" action="<?php echo $this->get_url('permission'); ?>"> 
	<div class="options">
	<h3><?php _e('Permissions', SLIMSTAT_DOMAIN); ?></h3>
<?php $hidden_text = __('Ignore Track') . __('View Stats') . __('Manage Options'); ?>
	<table cellspacing="3" cellpadding="3" class="widefat" style="width:750px"> 
	<thead>
	<tr valign="top">
		<th width="25%" scope="row"><?php _e('Role Name:', SLIMSTAT_DOMAIN); ?></th> 
		<th><?php _e('Role Permissions:', SLIMSTAT_DOMAIN); ?></th>
	</tr>
	</thead>
	<tbody>
<?php
		foreach ($wp_roles->role_names as $rolekey => $role_name) {
?>
	<tr valign="top">
		<th width="25%" scope="row"><?php if (function_exists('translate_user_role')) echo translate_user_role($role_name); elseif (function_exists('translate_with_context')) echo translate_with_context($role_name); else echo $role_name; ?></th> 
		<td>
<?php 
			foreach (array_values($SlimCfg->caps['administrator']) as $cap) {
				if ( $rolekey == 'administrator' ) {
?>
		<span class="input_checkbox input_checkbox_admin">
		<label for="<?php echo $cap.'_'.$rolekey; ?>"><?php echo ucwords(str_replace(array('_slimstat', '_'), array('', ' '), $cap)); ?></label>
		<input id="<?php echo $cap.'_'.$rolekey; ?>" type="hidden" name="ssex_perm[<?php echo $rolekey; ?>][]" value="<?php echo $cap; ?>" />
		</span>
<?php
				} else {
?>
		<span class="input_checkbox">
		<label for="<?php echo $cap.'_'.$rolekey; ?>"><?php _e( ucwords(str_replace(array('_slimstat', '_'), array('', ' '), $cap)), SLIMSTAT_DOMAIN); ?></label>
		<input id="<?php echo $cap.'_'.$rolekey; ?>" type="checkbox" name="ssex_perm[<?php echo $rolekey; ?>][]" value="<?php echo $cap; ?>"<?php checked(true, in_array($cap, (array)$SlimCfg->caps[$rolekey])); echo $disabled; ?> />
		</span>
<?php 
				}
			}
?>
		</td> 
	</tr>
<?php 
		}
?>
	</tbody>
	</table>
	<br style="clear:both;" />
	<p class="submit">
	<input type="submit" name="update_options" value="<?php _e('Update Options', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
	</div>
  </form>
</div><!-- wrap -->
<?php
	}

	function sanitize_url($url, $host=false) {
		global $SlimCfg;
		$url = sanitize_url($url, array('http', 'https'));
		if ('' == $url || 'http://' == $url || 'https://' == $url)
			return '';
		if ($host) {
			return $SlimCfg->get_url_info($url, 'host');
		}
		return $url;
	}

	function manage_sites(&$sites) {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('manage_options'))
			return;
		$message = '';
		$class = 'updated';
		if ( isset($_POST['save_sites_submit']) && isset($_POST['ssex_sites']) && is_array($_POST['ssex_sites']) ) {
			foreach($_POST['ssex_sites'] as $id => $site) {
				if ($id < 2) continue;
				$site = array_map(create_function('$a', 'return trim(trim($a, "/"));'), $site);
				$site['home'] = $site['home'] ? $this->sanitize_url($site['home']) : '';
				$site['host'] = $site['home'] ? $SlimCfg->get_url_info($site['home'], 'host') : '';
				if ('' == $site['home']) continue;
				$sites[$id] = $site;
			}
			update_option('wp_slimstat_ex_sites', $sites);
			$message = __('Site options saved.', SLIMSTAT_DOMAIN);
		} else if ( isset($_POST['ssex_add_site']) && is_array($_POST['ssex_add_site']) ) {
			$this->tmp_site = $_POST['ssex_add_site'];
			if ( isset($sites[$this->tmp_site['id']]) ) {
				$class = 'error';
				$message = sprintf(__('Site ID(%s) already in use.', SLIMSTAT_DOMAIN), $this->tmp_site['id']);
			} elseif (empty($this->tmp_site['home'])) {
				$class = 'error';
				$message = __('Home URL for new external site is empty.', SLIMSTAT_DOMAIN);
			} else {
				$this->tmp_site = array_map(create_function('$a', 'return trim(trim($a, "/"));'), $this->tmp_site);
				$this->tmp_site['home'] = $this->sanitize_url($this->tmp_site['home']);
				$this->tmp_site['host'] = $this->tmp_site['home'] ? $SlimCfg->get_url_info($this->tmp_site['home'], 'host') : '';
				if ( '' == $this->tmp_site['home'] ) {
					$class = 'error';
					$message = __('It seems that entered URL is not valid.', SLIMSTAT_DOMAIN);
				} else {
					$sites[$this->tmp_site['id']] = array('host'=>$this->tmp_site['host'], 'home'=>$this->tmp_site['home']);
					update_option('wp_slimstat_ex_sites', $sites);
					$message = __('Site added.', SLIMSTAT_DOMAIN);
					$this->tmp_site = array();
				}
			}
		}
		if ( '' != $message )
			echo '<div class="'.$class.' fade"><p>'.$message.'</p></div>';
	}

	function option_sites() {
		global $SlimCfg, $table_prefix, $wpdb;
		$sites = $SlimCfg->get_sites('edit');
		$this->manage_sites($sites);
		$pageurl = $this->get_url('site');
?>
	<div class="updated slim_msg">
	<p><?php printf(__('You can specify external sites by defining \'SITE ID\' on tracking code.<br />Check your code <a href="%s">below</a>.', SLIMSTAT_DOMAIN), $this->get_url('site') . '#slimstat_tracking_code'); ?></p>
	</div>

<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('options-general'); ?>
	<h2><?php _e('Manage External Sites', SLIMSTAT_DOMAIN); ?></h2>
	<div class="options">

	<form name="slimstat_manage_sites" method="post" action="<?php echo $pageurl; ?>"> 
	<h3><?php _e('Your External Sites', SLIMSTAT_DOMAIN); ?></h3>
	<table width="100%" cellpadding="3" cellspacing="3" class="widefat" style="width: 650px;">
	<thead>
	<tr>
		<th class="vers"><?php _e('ID', SLIMSTAT_DOMAIN); ?></th>
		<!-- <th><?php _e('Host Name', SLIMSTAT_DOMAIN); ?>*</th> -->
		<th><?php _e('Site Home URL', SLIMSTAT_DOMAIN); ?>*</th>
	</tr>
	</thead>
	<tbody>
	<tr class="alternate">
		<th class="vers">1
		<!-- <input type="hidden" name="ssex_sites[1][host]" value="" /> -->
		<input type="hidden" name="ssex_sites[1][home]" value="" />
		</th>
		<td><?php _e('default site', SLIMSTAT_DOMAIN); ?></td>
		<!-- <td><?php _e('default site', SLIMSTAT_DOMAIN); ?></td> -->
	</tr>
<?php
			$alt = 'alternate';
			foreach ($sites as $id => $site) {
				$alt = $alt == 'alternate' ? '' : 'alternate';
				if ($id < 2)
					continue;
?>
	<tr class="<?php echo $alt; ?>">
		<th class="vers"><?php echo $id; ?></th>
		<!-- <td>
		<input name="ssex_sites[<?php echo $id; ?>][host]" class="code" type="text" value="<?php echo attribute_escape($site['host']); ?>" />
		</td> -->
		<td>
		<input name="ssex_sites[<?php echo $id; ?>][home]" size="40" class="code" type="text" value="<?php echo attribute_escape($site['home']); ?>" />
		</td>
	</tr>
<?php
			}
?>
	</tbody>
	</table>
	<p class="submit">
	<input type="submit" name="save_sites_submit" value="<?php _e('Save Sites', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
	</form>

	<h3><?php _e('Add Site', SLIMSTAT_DOMAIN); ?></h3>
	<form name="slimstat_add_site" method="post" action="<?php echo $pageurl; ?>"> 
	<table width="100%" cellpadding="3" cellspacing="3" class="widefat" style="width: 650px;">
	<thead>
	<tr class="alternate">
		<th class="vers"><?php _e('ID', SLIMSTAT_DOMAIN); ?>*</th>
		<!-- <th><?php _e('Host Name', SLIMSTAT_DOMAIN); ?>*</th> -->
		<th><?php _e('Site Home URL', SLIMSTAT_DOMAIN); ?>*</th>
	</tr>
	</thead>
<?php if ( !isset($this->tmp_site['id']) ) $this->tmp_site['id'] = max(array_keys($sites)) + 1; ?>
	<tbody>
	<tr>
		<td class="vers">
		<input type="hidden" name="ssex_add_site[id]" value="<?php echo $this->tmp_site['id']; ?>" /><?php echo $this->tmp_site['id']; ?>
		</td>
		<!-- <td><input type="text" name="ssex_add_site[host]" class="code" value="<?php echo $this->tmp_site['host']; ?>" /></td> -->
		<td><input type="text" name="ssex_add_site[home]" class="widefat" value="<?php echo attribute_escape($this->tmp_site['home']); ?>" /></td>
	</tr>
	</tbody>
	</table>
	<p class="submit">
	<input type="submit" name="add_site_submit" value="<?php _e('Add Site', SLIMSTAT_DOMAIN) ?> &raquo;" />
	</p>
	</form>
	</div>

	<div class="options">
 <h3><?php _e('SlimStat-Ex external tracking', SLIMSTAT_DOMAIN); ?></h3>
  <ul>
<?php if ($SlimCfg->is_wpmu) { ?>
		<li><?php _e('External tracking does not support Wordpress-MU by now.', SLIMSTAT_DOMAIN) ?></li>
<?php } else { ?>
		<li><?php _e('If you want to track external PHP web tools on your server, rename <strong>external-config-sample.php</strong> to <strong>external-config.php</strong> and change some values refer to code below', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php _e('As you see, It almost same as <code>wp-config.php</code> file.', SLIMSTAT_DOMAIN); ?>
		<br />
	  <div class="slimstat_code">
		&lt;?php
		<br />
		/* External Track Config<br />
		---------------------------------------------------------------*/<br />
		// ** MySQL settings ** //<br />
		<code>$slimtrack_ext = array();</code><br />
		<code>$slimtrack_ext['DB_NAME']</code> = '<?php echo DB_NAME; ?>';&nbsp;&nbsp;&nbsp;&nbsp;// The name of the database<br />
		<code>$slimtrack_ext['DB_USER']</code> = '<?php echo DB_USER; ?>';&nbsp;&nbsp;&nbsp;&nbsp;// Your MySQL username<br />
		<code>$slimtrack_ext['DB_PASSWORD']</code> = '<?php echo DB_PASSWORD; ?>';&nbsp;&nbsp;// ...and password<br />
		<code>$slimtrack_ext['DB_HOST']</code> = '<?php echo DB_HOST; ?>';&nbsp;&nbsp;&nbsp;// 99% chance you won't need to change this value<br />
		<code>if(!defined('DB_CHARSET')) define('DB_CHARSET', '<?php echo DB_CHARSET; ?>');</code><br />
		<code>if(!defined('DB_COLLATE')) define('DB_COLLATE', '<?php echo DB_COLLATE; ?>');</code><br />
		<br /><!-- 
		// Change SECRET_KEY to the same value as your wp-config.php file<br />
		<code>if(!defined('SECRET_KEY')) define('SECRET_KEY', '<?php echo SECRET_KEY; ?>');</code> // Change this to a unique phrase.<br />
		<br /> -->
		// Change table_prefix to the same value as your wp-config.php file<br />
		<code>$slimtrack_ext['table_prefix']</code>  = '<?php echo $table_prefix; ?>';&nbsp;&nbsp;// Only numbers, letters, and underscores please!<br />
		<br />
		<code>if (!defined('SLIMSTAT_USER_AGENT'))<br />
			&nbsp;define('SLIMSTAT_USER_AGENT', false); // set this true to save user-agent data.<br />
		<br />
		?&gt;
		</div>
		</li>
		<li id="slimstat_tracking_code"><?php _e('Put the line of code below on any page you want to track.', SLIMSTAT_DOMAIN); _e('(recommended)', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php printf(__('Set "%1$s" to one that you\'ve defined on <a href="%2$s">Site Manager</a> page.', SLIMSTAT_DOMAIN), 'SLIMSTAT_SITE_ID', $this->get_url('site')); ?>
		<br />
		<div class="slimstat_code">
		&lt;?php <span class="fontred">define('SLIMSTAT_SITE_ID', 1);</span>  include_once("<?php echo ABSPATH; ?>wp-content/plugins/<?php echo $SlimCfg->basedir; ?>/lib/external.php"); ?&gt;
		</div>
		</li>
		<li><?php _e('You can track pages with javascript also. Insert lines below to bottom of page(just before &lt;/body&gt;) you want to track.', SLIMSTAT_DOMAIN); ?>
		<br />
		<?php printf(__('Set "%1$s" to one that you\'ve defined on <a href="%2$s">Site Manager</a> page.', SLIMSTAT_DOMAIN), 'SLIMSTAT_SITE_ID', $this->get_url('site')); ?>	
		<br />
		<div class="slimstat_code">
		&lt;script type='text/javascript' src='<?php echo $SlimCfg->pluginURL; ?>/lib/external.js.php?'>&lt;/script&gt;<br />
		&lt;script type='text/javascript'&gt;<br />
		<span class="fontred">var SLIMSTAT_SITE_ID = 1;</span>&nbsp;&nbsp;// your external site id.<br />
		var _SlimStatExTrack = SlimStatExTrack();<br />
		&lt;/script&gt;
		</div>
		</li>
<?php } ?>
  </ul>
 	</div>

</div><!-- wrap -->
<?php
	}

	function manage_admin() {
		global $SlimCfg, $wpdb;
		if (!$SlimCfg->has_cap('manage_options'))
			return;
		if ( isset($_POST['slimstat_fix_country_data']) ) {
			require_once(SLIMSTATPATH . 'lib/ss-admin/_functions.php');
			$fixed = $ssAdmin->update_country_data('common');
			if ($fixed)
				$fixed = $ssAdmin->update_country_data('feed');
			if ($fixed)
				echo '<div class="updated"><p>'.__('Unknown country data fixed.', SLIMSTAT_DOMAIN).'</p></div>';
			else 
				echo '<div class="error"><p>'.__('Failed fix unknown country data.', SLIMSTAT_DOMAIN).'</p></div>';
		}
	}

	function option_admin() {
		global $SlimCfg, $table_prefix;
		if (!$SlimCfg->has_cap('manage_options')) {
			echo '<div class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$this->manage_admin();
?>
<div class="wrap">
<?php if (function_exists('screen_icon')) screen_icon('tools'); ?>
	<h2><?php _e('SlimStat-Admin', SLIMSTAT_DOMAIN) ?></h2>
	<div class="options">
  <h3><?php _e('SlimStat-Ex Admin Tools', SLIMSTAT_DOMAIN); ?></h3>
  <ul>
		<li><?php printf( __('Delete SlimStat data more than &quot;DB max-age(%s)&quot; days old', SLIMSTAT_DOMAIN), ($SlimCfg->option['dbmaxage']==0 ? 'disabled' : $SlimCfg->option['dbmaxage']) ); ?></li>
		<li><?php _e('SlimStat-Ex performance tool', SLIMSTAT_DOMAIN) ?></li>
<?php /* does not supports upgrade from shortstat or slimstat anymore ?>
		<li><?php _e('Upgrade from Wp-SlimStat(0.92)', SLIMSTAT_DOMAIN) ?></li>
		<li><?php _e('Upgrade from Wp-ShortStat', SLIMSTAT_DOMAIN) ?></li>
<?php */ ?>
	  <li><?php _e('Display available modules', SLIMSTAT_DOMAIN) ?></li>
  </ul>
  <h4><a href="<?php echo $this->admin_url; ?>/index.php"><?php _e('Go to SlimStat Admin Page', SLIMSTAT_DOMAIN) ?> &raquo;</a></h4>
 	</div>

<?php if (true !== SLIMSTAT_EXTERNAL_IPTC) { ?>

<?php if ( ($SlimCfg->geoip == 'country' || !$SlimCfg->geoip) && function_exists('get_transient') ) { // requires WP 2.8 or greater ?>
	<div class="options">
  <h3><?php _e('Update GeoIP Data', SLIMSTAT_DOMAIN); ?></h3>
<?php 
	if ( isset($_POST['slimstat_update_file']) || isset($_POST['upgrade']) ) {
		if ( isset($_REQUEST['update_geoip_data']) ) {
			require(SLIMSTATPATH . 'lib/update.php');
			$title = __('Update GeoIP Data', SLIMSTAT_DOMAIN);
			$nonce = 'update_geoip_data';
			$url = 'admin.php?page=wp-slimstat-ex-admin&update_geoip_data=1';
			$db_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz';

			$upgrader = new GeoIP_Upgrader( new GeoIP_Upgrader_Skin( compact('title', 'nonce', 'url') ) );
			$upgrader->update($db_url);
			$SlimCfg->init_geoip();
		}
	}
?>

<?php
		if (!$SlimCfg->geoip)
			$geo_db_date = 0;
		else {
			$geo_db_date = (int)$SlimCfg->geo_db_date();
			$geo_db_date = strtotime($geo_db_date);
			$next_db_time = mktime( 0, 0, 0, date("n", $geo_db_date)+1, 2, date("Y", $geo_db_date) ); // 2nd of next month
		}
//		$next_db_time = $geo_db_date;
?>
		<p><?php _e('<a href="http://www.maxmind.com/">MaxMind</a>\'s geoip database is updated monthly, at the beginning of each month.', SLIMSTAT_DOMAIN); ?></p>
		<form name="slimstat_update_file_form" method="post" action="<?php echo $this->get_url('admin'); ?>">
		<input name="slimstat_update_file" type="hidden" value="1" />
		<span class="updated">
<?php
	if (!$geo_db_date || $geo_db_date < 0) {// PHP 4, PHP 5.0 returns '-1' on failure.
		_e('Your GeoIP data is corrupted or deleted.', SLIMSTAT_DOMAIN);
		$button_text = __('Re-install GeoIP data', SLIMSTAT_DOMAIN);
	} elseif ($next_db_time > time()) { 
		_e('Your GeoIP database is up to date, you don\'t have to update it.', SLIMSTAT_DOMAIN);
		$button_text = __('Force update GeoIP data', SLIMSTAT_DOMAIN);
	} else {
		_e('Updated GeoIP database is available.', SLIMSTAT_DOMAIN);
		$button_text = __('Update GeoIP data', SLIMSTAT_DOMAIN);
	}
?>
			</span>
		<input class="button" type="submit" name="update_geoip_data" value="<?php echo $button_text; ?> &raquo;" />
		</form>
	</div>
<?php } ?>

	<div class="options">
  <h3><?php _e('Fix Country Data', SLIMSTAT_DOMAIN); ?></h3>
	<p><?php _e('You can fix \'unknown\' country data after updating GeoIP database with new one.', SLIMSTAT_DOMAIN); ?></p>
	<form name="slimstat_fix_country_data_form" method="post" action="<?php echo $this->get_url('admin'); ?>">
	<input name="slimstat_fix_country_data" type="hidden" value="1" />
	<input class="button" type="submit" name="fix_country_data" value="<?php _e('Fix country data', SLIMSTAT_DOMAIN); ?> &raquo;" />
	</form>
	</div>
<?php } ?>

</div>
<?php
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new wp_slimstat_ex_options();
		}
		return $instance[0];
	}

}

?>