<?php
/*
Module Name : Stats On Blog
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL

Description ::

	You can check displayable module list from your SlimStat-Admin page
*/

/* DO NOT EDIT BELOW LINES */
if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSBlogStats extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'Stats on Blog',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Display current modules you want - maybe on page or post',
		'version' => '0.3',
		'type' => 1,
	);

	// About displayable modules of this Pin
	var $Moinfo = array();
	var $op;

	function SSBlogStats() {
		$this->op = $this->get_option('stats_on_blog');
//		if ($this->op['ajax_lib']) 
//			add_action('wp_print_scripts', array(&$this, 'enqueue_scripts'));
		add_action('wp_head', array(&$this, 'wp_head'), 20);
		add_filter('the_content', array(&$this, '_filter'), 10);
	}

	function pin_compatible() {
		global $SlimCfg;
		if ($SlimCfg->version < '1.6') {
			return array	('compatible' => false, 'message' => 'Stats On Blog is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function pin_actions() {
		return array( 'options' => 1, 'extra_table' => 0 );
	}

	function pin_update_options() {
		if (!isset($_POST['stats_on_blog']) || !is_array($_POST['stats_on_blog']))
			return;
		$this->op['use_filter'] = (int)$_POST['stats_on_blog']['use_filter'];
		$this->op['ajax_lib'] = trim($_POST['stats_on_blog']['ajax_lib']);
		$this->update_option('stats_on_blog', $this->op);
	}

	function pin_options() {
		global $SlimCfg;
?>
<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Use Content Filter:', SLIMSTAT_DOMAIN) ?></th> 
	<td><?php _e('Replace {SLIMSTAT|1,2,3...} on content with displayable modules.', SLIMSTAT_DOMAIN); ?><br />
	<?php printf(__('Displayable modules are available at <a href="%s">SlimStat-Admin page</a>.', SLIMSTAT_DOMAIN), $SlimCfg->pluginURL.'/lib/ss-admin/modulelist.php'); ?><br />
	<?php _e('You can manually insert &lt;?php wpSSBlogStats(); ?&gt; on your template file', SLIMSTAT_DOMAIN); ?><br />
		<select name="stats_on_blog[use_filter]">
			<option value="0"<?php if (!$this->op['use_filter']) { ?> selected="selected"<?php } ?>>NO</option>
			<option value="1"<?php if ($this->op['use_filter']) { ?> selected="selected"<?php } ?>>YES</option>
		</select>
		<input type="hidden" name="stats_on_blog[ajax_lib]" value="0" />
		</td>
</tr>
<?php /*if (function_exists('wp_print_scripts')) {// AJAX on blog disabled ?>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('AJAX Library:', SLIMSTAT_DOMAIN) ?></th> 
	<td><?php _e('Select javascript library for AJAX features or NONE for no AJAX', SLIMSTAT_DOMAIN); ?><br />
			<select name="stats_on_blog[ajax_lib]">
			<option value="0"<?php selected(0, (int)$this->op['ajax_lib']); ?>>NONE</option>
			<option value="prototype"<?php selected('prototype', $this->op['ajax_lib']); ?>>Prototype</option>
			<option value="jquery"<?php selected('jquery', $this->op['ajax_lib']); ?>>jQuery</option>
		</select></td>
</tr>
<?php }*/ ?>
</table>
<?php
	}

	function _replace($m) {
		$m = trim($m[2]);
		if (empty($m))
			$IDs = array(1,2,3);
		else 
			$IDs = explode(',', str_replace(array('|', ' '), '', $m));
		return $this->print_stats($IDs, false);
	}

	function _filter($content) {
		if (!$this->op['use_filter'])
			return $content;
		return preg_replace_callback('/(<p>)*\s*{SLIMSTAT(.*?)}\s*\n*(<\/p>)*/', array(&$this, '_replace'), $content);
	}

	function wp_head() {
		global $SlimCfg;
		?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $SlimCfg->pluginURL; ?>/pins/SSBlogStats/blogstats.css?ver=<?php echo $this->Pinfo['version']; ?>" />
		<?php /* ?>
		<script type="text/javascript">//<![CDATA[
		SlimStatL10n = {
			url: "<?php echo $SlimCfg->pluginURL; ?>/lib",
			f: "<?php echo $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode']; ?>",
			fi: "<?php echo $SlimCfg->get['fi_encode']; ?>",
			nonce_m: "<?php echo wp_create_nonce('slimstat-load-module'); ?>",
			nonce_p: "<?php echo wp_create_nonce('slimstat-load-panel'); ?>"
		}
		//]]></script>
		<?php */
	}

	function enqueue_scripts() {
		global $SlimCfg;
		if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/') || strstr($_SERVER['REQUEST_URI'], 'wp-admin/'))
			return;
		$js_path = $SlimCfg->pluginURL.'/js/';
		switch($this->op['ajax_lib']) {
			case 'prototype':
			default:
			wp_register_script('moo.fx.base', $js_path.'moo.fx.base.js', array('prototype'), '2.0');
			wp_enqueue_script('ajax-slimstat-proto', $js_path.'ajax-slimstat-proto.js', array('prototype', 'moo.fx.base'), $SlimCfg->version);
			break;
			case 'jquery':
			wp_enqueue_script('ajax-slimstat-jquery', $js_path.'ajax-slimstat-jquery.js', array('jquery-form'), $SlimCfg->version);
			break;
		}
	}

	function print_stats($IDs = array(1,2,3), $echo = true) {
		global $SlimCfg, $wpdb;
		if (!is_array($IDs) || empty($IDs))
			return;
//		if (!$this->op['ajax_lib'] || !function_exists('wp_print_scripts')) // only support AJAX with script-loader.php
			$SlimCfg->option['use_ajax'] = false;// AJAX on blog disabled.
		require_once(SLIMSTATPATH . 'lib/modules.php');
		$active_pins = $this->_getPins(1, 3);

		$wrapper .= '<a id="slm2" name="slm2"></a>'."\n";
		$wrapper .= '<div id="wp_slimstat">'."\n";
		$nav = '<p class="module_nav">'.__('Modules', SLIMSTAT_DOMAIN).' : ';
		$output = '';

		foreach($IDs as $id) {
			if (empty($id))
				continue;
			$module = SSFunction::id2module($id);
			if ($module) {
				$output .= call_user_func(array('SSModule', $module), SLIMSTAT_DEFAULT_FILTER);
				$nav .= '[ <a href="#module_'.$id.'">'.SSFunction::get_title($id).'</a> ] ';
			} elseif ($id > 10000) {
				$pinid = floor($id/100) - 100;
				$mo = $id - (($pinid+100)*100) - 1;
				$pin = SSFunction::pin_mod_info($pinid);
				$pin_file = SLIMSTAT_PINPATH . $pin['name'] . '/pin.php';

				if (!$pin || !file_exists($pin_file) || !$pin['active'] || $pin['type'] == 1 || !isset($pin['modules'][$mo])) {
					$output .= '<p>Wrong module ID '.$id.'</p>';
					continue;
				}
				if ($pin['type'] == 0)// 'Display only' Pins are not included yet.
					include_once($pin_file);

				$nav .= '[ <a href="#module_'.$id.'">'.$pin["modules"][$mo]["title"].'</a> ] ';
				$temp_pin = new $pin["name"]();
				$output .= call_user_func(array(&$temp_pin, $pin["modules"][$mo]["name"]), SLIMSTAT_DEFAULT_FILTER);
			}
		}
		$nav .= '<span id="slimloading"> ( Loading... ) </span>';
		$nav .= '</p>';
		$wrapper .= $nav.$output.'</div>';
		if ($echo) 
			echo $wrapper;
		else 
			return $wrapper;
	}

}//end of class

/* Print Stats on Blog
------------------------------------------------------------------------*/
$wpSSBlogStats = new SSBlogStats();

function wpSSBlogStats($IDs = array(1,2,3), $echo = true) {
	global $wpSSBlogStats;
	return $wpSSBlogStats->print_stats($IDs, $echo);
}
?>