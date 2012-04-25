<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

require_once(SLIMSTATPATH . 'lib/modules.php');
/*
if ($SlimCfg->option['usepins']) {
	require_once(SLIMSTATPATH . 'lib/pins.php');
}
*/
if (!class_exists('SSDisplay')) :
class SSDisplay {

	function SSDisplay() {
	}

	function menu_item($args) {

	}

	function displayStats() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('view_slimstat_stats')) {
			echo '<div id="message" class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$href = '?page=wp-slimstat-ex&amp;panel=';
		$nonce = '&amp;action=request_panel&amp;_wpnonce='.wp_create_nonce('slimstat-view-stats');
		$nav_items = array(
			1 => __('Summary', SLIMSTAT_DOMAIN),
			2 => __('Feeds', SLIMSTAT_DOMAIN),
			3 => __('Details', SLIMSTAT_DOMAIN),
			);
		if ($SlimCfg->option['usepins']) {
			$nav_items += SSPins::get_pin_nav();
		}
		$nav_counts = count($nav_items);
	?>
<div class="wrap wp_slimstat_wrap">
<?php if (function_exists('screen_icon')) screen_icon('wp-slimstat-ex'); ?>
	<h2><?php _e('SlimStat-Ex', SLIMSTAT_DOMAIN) ?> <span>:: <?php bloginfo('name'); ?></span></h2>
	<div id="wp_slimstat">
	<div class="slim_menu_wrap">
		<ul class="wp_slimstat_tabs" id="slim_menu">
		<?php 
		$i=0;
		foreach ($nav_items as $item => $name) {
			$class = ($item == $SlimCfg->get['pn']) ? ' slm_current' : '';
			if ($i == 0) $class .= ' first-item';
			elseif ($i == $nav_counts-1) $class .= ' last-item';
			$i++;
			$class_li = $class ? ' class="'.trim($class).'"' : '';
		?>
		<li<?php echo $class_li; ?>><div class="slm_toggle"><br /></div>
		<a id="slm<?php echo $item; ?>" class="ajax-request-link slm<?php echo $class; ?>" href="<?php echo $href . $item . $nonce; ?>"><?php echo $name; ?></a></li>
		<?php } ?>
		</ul>
		<!-- <span id="slimloading" style="float:left; display:none;"> <?php _e('( Loading ... )', SLIMSTAT_DOMAIN); ?> </span> -->
		
	</div><!-- #slim_menu_wrap -->
	<br style="clear:both;" />

	<div id="slim_main_wrap"><div id="slim_main">
	<?php if (!$SlimCfg->option['use_ajax']) SSDisplay::wp_slimstat_ajax_display(); ?>
	</div></div>
	<div id="donotremove">
		<span class="slim_poweredby"><a href="<?php echo $SlimCfg->plugin_home; ?>">Wp-SlimStat-Ex</a> <?php echo $SlimCfg->version; ?>, 
		<?php printf(__('based on %1$s and %2$s', SLIMSTAT_DOMAIN), '<a href="http://www.duechiacchiere.it/wp-slimstat/">Wp-SlimStat 0.9.2</a>',
		'<a href="http://wettone.com/code/slimstat">SlimStat</a>'); ?></span>
		<br />
		<span class="slim_metafooter"><?php _e("Data size", SLIMSTAT_DOMAIN) ?> : <?php echo SSFunction::_getTableSize(); ?> 
		(<?php _e("Feed", SLIMSTAT_DOMAIN) ?> : <?php echo SSFunction::_getTableSize('feed'); ?>)</span>
	</div><!-- donotremove -->
	</div><!-- wp_slimstat -->
</div><!-- wrap -->
	<?php
	}

	function wp_slimstat_ajax_display($p='') {
		global $SlimCfg;
		$p = !empty($p) ? (int)$p : $SlimCfg->get['pn'];
		if ($p < 100) {
			switch ($p) {
				case 5:
					SSDisplay::_displayConfig();
					break;
				case 3:
					SSDisplay::_displayDetails();
					break;
				case 2:
					SSDisplay::_displayFeeds();
					break;
				case 1:
				default:
					SSDisplay::_displaySummary();
					break;
			}
		} else if ($SlimCfg->option['usepins'] && isset($SlimCfg->pinid2modlue[$p]['name'])) {
			$pinName = $SlimCfg->pinid2modlue[$p]['name'];
			global ${$pinName};
			if (!is_a(${$pinName}, 'SSPins'))
				${$pinName} = new $pinName();
			$comp = ${$pinName}->pin_compatible();
			if (!$comp['compatible'])
				echo '<div class="error fade"><p>'.sprintf(__('This pin does not work with WP-SlimStat-Ex v<em>%s</em>', SLIMSTAT_DOMAIN), $SlimCfg->version).'</p></div>';
			else 
				${$pinName}->_displayPanel();
		} else {
			echo '<div class="error fade"><p>'.__('Invalid Pin ID', SLIMSTAT_DOMAIN).'</p></div>';
		}
	}

	function get_html($modules=array(), $filter=SLIMSTAT_DEFAULT_FILTER) {
		static $moid_found = false;
		if (empty($modules))
			return '';
		if (is_int($modules))
			$modules = array($modules);
		elseif (!is_array($modules)) {
			$modules = explode(',', trim($modules));
			$modules = array_filter($modules);
		}

		$html = '';
		$moid = (int) $_GET['moid'];
//		$moid_found = false;
		foreach ($modules as $module) {
			$func = false;
			if ( $moid && !$moid_found ) {// requested other module
				$fellows = SSFunction::fellow_links($module);
				if ( in_array($moid, $fellows) ) {
					$func = SSFunction::id2module($moid);
					$moid_found = true;
				}
			}
			if (!$func)
				$func = SSFunction::id2module($module);

			if ($func && method_exists('SSModule', $func))
				$html .= call_user_func(array('SSModule', $func), $filter);
		}
		return $html;
	}

	function _get_html($modules=array()) {
		static $moid_found = false;
		if (empty($modules))
			return '';

		$html = '';
		$moid = (int) $_GET['moid'];

		foreach (array_keys($modules) as $module) {
			$func = false;
			if ( $moid && !$moid_found ) {// requested other module
				$fellows = SSFunction::fellow_links($module);
				if ( in_array($moid, $fellows) ) {
					$func = SSFunction::id2module($moid);
					$moid_found = true;
				}
			}
			if (!$func)
				$func = SSFunction::id2module($module);

			if ($func && method_exists('SSModule', $func))
				$html .= call_user_func(array('SSModule', $func), $modules[$module]);
		}
		return $html;
	}

	function _displaySummary() {
		$modules = array(
			1 => '',
			2 => '',
			3 => '',
			92 => '',
			5 => array('style'=>'wide'),
		);

//		echo SSDisplay::get_html(array_keys($modules));
		echo SSDisplay::_get_html($modules);
	}

	function _displayFeeds() {
		echo SSFunction::_getFilterForm();
		$modules = array(
			1 => '',
			16 => '',
			91 => '',
			19 => '',
			20 => array('style'=>'wide'),
			10 => array('style'=>'wide'),
			13 => ''
		);

		echo SSDisplay::_get_html($modules);
	}

	function _displayDetails() { // show all filterd results
		echo SSFunction::_getFilterForm();
		$modules = array(
			1 => '',
			16 => '',
			91 => '',
			19 => '',
			20 => array('style'=>'wide'),
			10 => array('style'=>'wide'),
			11 => ''
		);

		echo SSDisplay::_get_html($modules);
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSDisplay();
		}
		return $instance[0];
	}

}// end of class
endif;

/*if ($SlimCfg->option['usepins']) {
	SSPins::_incPins(0);
}*/

?>