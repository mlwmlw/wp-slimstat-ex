<?php
/*
Plugin Name: WP-SlimStat-Ex
Plugin URI: http://082net.com/tag/wp-slimstat-ex/
Description: Track your blog stats. Based on <a href="http://www.duechiacchiere.it/">Mr. Coolmann</a>'s <a href="http://www.duechiacchiere.it/wp-slimstat/">Wp-SlimStat</a>. 
Version: 2.1.2
Author: Cheon, Young-Min
Author URI: http://082net.com/
*/

/*
ABOUT MODIFICATION ::
	Almost php and sql codes has written by Mr. Coolmann(http://www.duechiacchiere.it)
	What I've done is intergrating Ajax, constructing Pins(plugable panel) condition and some little patches.
	Thanks, Coolmann.

License ::
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

http://www.gnu.org/licenses/gpl.txt

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
______________________________________________________________*/

/*
Powered by
	jQuery :: http://jquery.com
	icons :: Mark James(http://www.famfamfam.com/), http://wefunction.com/2008/07/function-free-icon-set/, http://www.pinvoke.com/
	Ajax.History :: Siegfried Puchbauer <rails-spinoffs@lists.rubyonrails.org>
	SweetTitles :: Dustin Diaz (http://www.dustindiaz.com)
	IP-Lookup :: http://ip-lookup.net/ and http://dnsstuff.com
	GeoIp :: http://maxmind.com
*/

// Protect the script from direct access
if ( !defined('ABSPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

define('SLIMSTATPATH',  realpath( dirname( __FILE__ ) ) . '/');

// include SlimStat config file
include(SLIMSTATPATH . 'wp-slimstat-ex-config.php');

// localize plugin
load_plugin_textdomain(SLIMSTAT_DOMAIN, 'wp-content/plugins/'.$SlimCfg->basedir.'/lang');

class wp_slimstat_ex {

	function _upgrade_notice() {
		global $SlimCfg;
		$slimstat_admin_url = get_option('siteurl') . '/wp-content/plugins/' . $SlimCfg->basedir . '/lib/ss-admin/upgrade.php';
		echo "<div id='wpssex_upgrade_notice' class='error fade'><p>".sprintf(__('WP-SlimStat-Ex needs <a href=\'%s\'>upgrade</a>', SLIMSTAT_DOMAIN), $slimstat_admin_url)."</p></div>";
	}

	function _not_compatible() {
		echo "<div id='wpssex_not_compatible' class='error fade'><p>".__('WP-SlimStat-Ex is NOT active. WP-SlimStat-Ex requires WP 2.5 or greater.', SLIMSTAT_DOMAIN)."</p></div>";
	}

	function option_page_css() {
		global $SlimCfg;
			echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/options.css?ver='.$SlimCfg->version.'" />';
	}

	function slimstat_admin_head() {
		global $SlimCfg;
		$load_css = '';
		$load_css .= '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/ui.daterangepicker.css?ver='.$SlimCfg->version.'" />';
		$load_css .= '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/ui-lightness/jquery-ui-1.7.1.custom.css?ver='.$SlimCfg->version.'" />';
		$load_css .= '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/slimstat.css?ver='.$SlimCfg->version.'" />';
		$load_css .= ($SlimCfg->option['nice_titles'])?"\n".'<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/sweetTitles.css?ver='.$SlimCfg->version.'" />':'';
		$load_css .= '<!--[if lte IE 7]>
<link rel="stylesheet" href="'.$SlimCfg->pluginURL.'/css/ie.css?ver='.$SlimCfg->version.'" type="text/css" media="all" />
<![endif]-->';
		echo "\n".'<!-- Added by Wp-SlimStat-Ex '.$SlimCfg->version.' -->'."\n".$load_css."\n";
	}

	function enqueue_script_admin() {
		global $SlimCfg, $wp_scripts;
		$src_path = $SlimCfg->pluginURL.'/js/';
		$SlimCfg->base_jslib = 'jquery';
		if (!$SlimCfg->option['use_ajax'])
			$SlimCfg->base_jslib = 'none';

		$deps = array('jquery', 'hoverIntent');

		if ($SlimCfg->wp_version < '2.7')
			wp_register_script('hoverIntent', $src_path.'hoverIntent.js', array('jquery'), '20090102');

		if ($SlimCfg->wp_version < '2.8') {// daterangepicker requires jQuery 1.3 or later.
			wp_deregister_script('jquery');
			wp_register_script('jquery', $src_path.'jquery.js', false, '1.3.2');
		}
		wp_register_script('jquery-ui-datepicker', $src_path.'ui.datepicker.js', array('jquery'), '1.7.1');
		wp_register_script('jquery-daterangepicker', $src_path.'daterangepicker.jQuery.js', array('jquery-ui-datepicker'), '20081023');
		$deps[] = 'jquery-daterangepicker';

		wp_register_script('swfobject', $src_path.'swfobject.js', false, '2.2b1');
		$deps[] = 'swfobject';

		if ($SlimCfg->option['nice_titles'] && !$wp_scripts->query('interface', 'queue')) {
			wp_register_script('jquery.sweetTitles', $src_path.'sweetTitles_jquery.js', array('jquery'), $SlimCfg->version);
			$deps[] = 'jquery.sweetTitles';
		}

		switch($SlimCfg->base_jslib) {
			case 'jquery':
			$deps[] = 'jquery-form';

			if ($SlimCfg->option['ajax_history']) {
				wp_register_script('jquery.history', $src_path.'jquery.history.js', array('jquery'), '20090606');
				$deps[] = 'jquery.history';
			}
			wp_register_script('slimstat-ex', $src_path.'ajax-slimstat-jquery.js', $deps, $SlimCfg->version);
			break;
			case 'none': default:
			wp_register_script('slimstat-ex', $src_path.'slimstat.js', $deps, $SlimCfg->version);
			break;
		}

		wp_localize_script('slimstat-ex', 'SlimStatL10n', array(
			'url' => $SlimCfg->pluginURL,
			'charset' => strtolower(get_option('blog_charset')),
			'_wpnonce' => wp_create_nonce('slimstat-view-stats'),
			'presetRanges_today' => __('Today', SLIMSTAT_DOMAIN),
			'presetRanges_week' => __('Last 7 days', SLIMSTAT_DOMAIN),
			'presetRanges_month' => __('Month to date', SLIMSTAT_DOMAIN),
			'presetRanges_year' => __('Year to date', SLIMSTAT_DOMAIN),
			'presetRanges_prevmonth' => __('The previous Month', SLIMSTAT_DOMAIN),
			'specificDate' => __('Specific Date', SLIMSTAT_DOMAIN),
			'allDatesBefore' => __('All Dates Before', SLIMSTAT_DOMAIN),
			'allDatesAfter' => __('All Dates After', SLIMSTAT_DOMAIN),
			'dateRange' => __('Date Range', SLIMSTAT_DOMAIN),
			'rangeStartTitle' => __('Start date', SLIMSTAT_DOMAIN),
			'rangeEndTitle' => __('End date', SLIMSTAT_DOMAIN),
			'nextLinkText' => __('Next', SLIMSTAT_DOMAIN),
			'prevLinkText' => __('Prev', SLIMSTAT_DOMAIN),
			'doneButtonText' => __('Done', SLIMSTAT_DOMAIN),
			'presetRanges_today' => __('Today', SLIMSTAT_DOMAIN),
			'presetRanges_today' => __('Today', SLIMSTAT_DOMAIN),
			));

		wp_enqueue_script('slimstat-ex');
	}

	function wp_slimstat_ex_admin_head() {
		do_action('wp_slimstat_ex_admin_head');
	}

	function slimstat_menu() {
		global $SlimCfg, $user_level;
		if (defined('SLIMSTAT_UPGRADE') && SLIMSTAT_UPGRADE == true)
			return;
		if (function_exists('add_management_page')) {
			require_once(SLIMSTATPATH . 'lib/display.php');
			require_once(SLIMSTATPATH . 'wp-slimstat-ex-options.php');
			$options =& wp_slimstat_ex_options::get_instance();
			$page = 'wp-slimstat-ex';

			$top = add_menu_page('SlimStat', 'SlimStat', 'view_slimstat_stats', $page, array('SSDisplay', 'displayStats'), $SlimCfg->pluginURL.'/css/slimstat.png');
			$option = add_submenu_page($page, __('General Options', SLIMSTAT_DOMAIN), __('General Options', SLIMSTAT_DOMAIN), 'manage_slimstat_options', 'wp-slimstat-ex-option', array(&$options, 'options_page'));
			if ( $SlimCfg->option['usepins'] ) {
				$pin = add_submenu_page($page, __('Pins', SLIMSTAT_DOMAIN), __('Pins', SLIMSTAT_DOMAIN), 'manage_slimstat_options', 'wp-slimstat-ex-pin', array(&$options, 'option_pins'));
			}
			$exclusion = add_submenu_page($page, __('Exclusions', SLIMSTAT_DOMAIN), __('Exclusions', SLIMSTAT_DOMAIN), 'manage_slimstat_options', 'wp-slimstat-ex-exclusion', array(&$options, 'option_exclusions'));
			$permission = add_submenu_page($page, __('Permissions', SLIMSTAT_DOMAIN), __('Permissions', SLIMSTAT_DOMAIN), 'manage_options', 'wp-slimstat-ex-permission', array(&$options, 'option_permissions'));
			$site = add_submenu_page($page, __('External Sites', SLIMSTAT_DOMAIN), __('External Sites', SLIMSTAT_DOMAIN), 'manage_options', 'wp-slimstat-ex-site', array(&$options, 'option_sites'));
			$admin = add_submenu_page($page, __('SlimStat-Admin', SLIMSTAT_DOMAIN), __('SlimStat-Admin', SLIMSTAT_DOMAIN), 'manage_options', 'wp-slimstat-ex-admin', array(&$options, 'option_admin'));
//			$menu = compact($top, $option, $exclusion, $permission, $site, $admin);

			// scripts & styles
			add_action('admin_print_scripts-'.$top, array('wp_slimstat_ex', 'enqueue_script_admin'));
			add_action('admin_head-'.$top, array('wp_slimstat_ex', 'slimstat_admin_head'));
			// option page style
			if (wp_slimstat_ex::is_slimstat_option_page('all'))
			add_action('admin_head', array('wp_slimstat_ex', 'option_page_css'));
			// admin head actions for Pins
			add_action('admin_head-'.$top, array('wp_slimstat_ex', 'wp_slimstat_ex_admin_head'));
		}
	}

	function is_slimstat_page() {
		return ( is_admin() && 'wp-slimstat-ex' == trim($_GET['page']) );
	}

	function is_slimstat_option_page($page='option') {
		global $SlimCfg;
		if ( !is_admin() )
			return false;
		switch ($page) {
			case 'option': case 'pin': case 'exclusion': case 'admin': case 'permission': case 'site':
				return ( trim($_GET['page']) == 'wp-slimstat-ex-' . $page );
			break;
			case 'all': default:
				return ( strpos($_GET['page'], 'wp-slimstat-ex-') === 0 );
			break;
		}
	}

	function set_options($force=false) {// reset options
		global $SlimCfg;
		// set options if not exists
		if ($force || !get_option('wp_slimstat_ex')) {
			$SlimCfg->option = $SlimCfg->default_options();
			update_option('wp_slimstat_ex', $SlimCfg->option);
		}
		// set exclude options
		if ($force || !get_option('wp_slimstat_ex_exclude')) {
			$SlimCfg->exclude = $SlimCfg->default_exclusions();
			update_option('wp_slimstat_ex_exclude', $SlimCfg->exclude);
		}
		// set capabilities options
		if ($force || !get_option('wp_slimstat_ex_caps')) {
			$SlimCfg->caps = $SlimCfg->default_caps();
			$SlimCfg->check_caps(true);
			update_option('wp_slimstat_ex_caps', $SlimCfg->caps);
		}
	}

	function setup() {
		global $SlimCfg;
		if ($SlimCfg->wp_version < '2.5')
			return;
		require(SLIMSTATPATH . 'lib/setup.php');
		SSSetup::do_setup();
	}

	function check_current_version($install=false) {
		global $SlimCfg;

		$current = get_option('wp_slimstat_ex_version');
		if (!$install) {// we cannot check tables, there's may no slimstat tables yet.
			if (!$current || version_compare($current, $SlimCfg->last_db_update_version, '<'))
				return false;
			update_option('wp_slimstat_ex_version', $SlimCfg->version);
			return true;
		}

		if (!$current || $current == '0.1') {
			require_once(SLIMSTATPATH . 'lib/ss-admin/_functions.php');
			if (!isset($ssAdmin))
				$ssAdmin =& SSAdmin::get_instance();
			// check if Pins table has 'type' column
			$is_15 = $ssAdmin->maybe_add_column($SlimCfg->table_pins, 'type', '', true);
			if (!$is_15) {
				update_option('wp_slimstat_ex_version', '0.1');
				return false;
			}
			if (empty($SlimCfg->indexkey))
				$SlimCfg->indexkey = 	array('common'=>$SlimCfg->_getIndexKeys('common', true), 'feed'=>$SlimCfg->_getIndexKeys('feed', true));
			$is_16 = isset($SlimCfg->indexkey['common']['dt']) && isset($SlimCfg->indexkey['common']['remote_ip']);
			if (!$is_16) {
				update_option('wp_slimstat_ex_version', '1.5');
				return false;
			}
			$is_20 = $ssAdmin->maybe_add_column($SlimCfg->table_resource, 'rs_md5', '', true);
			if (!$is_20) {
				update_option('wp_slimstat_ex_version', '1.6');
				return false;
			}
			$is_21 = $ssAdmin->maybe_add_column($SlimCfg->table_resource, 'site_id', '', true);
			if (!$is_21) {
				update_option('wp_slimstat_ex_version', '2.0');
				return false;
			}
			update_option('wp_slimstat_ex_version', '2.1');
		}
		$current = get_option('wp_slimstat_ex_version');
		if (version_compare($current, $SlimCfg->last_db_update_version, '<'))
			return false;
		update_option('wp_slimstat_ex_version', $SlimCfg->version);
		return true;
	}

	function call_queried_object() {
		global $SlimCfg;
		global $wp_query, $wp_the_query;
		if ( !is_single() && !is_page() && !is_attachment() )
			return;
		// Fix queried object bug, if there's no wp_title on header.php of current theme.
		if (is_null($wp_the_query->queried_object_id))
			$wp_the_query->get_queried_object();
		return;
	}

	function plugin_update_row($file) {
		global $SlimCfg;
		if ($file != $SlimCfg->_basename(__FILE__))
			return;
		$r = get_option('wp_slimstat_ex_latest');
		if (!$r) {
			$r->last_checked = time() - 43200;
			$r->response->slug = 'wp-slimstat-ex';
			$r->response->new_version = $SlimCfg->version;
			update_option('wp_slimstat_ex_latest', $r);
		}
		$time_not_changed = isset( $r->last_checked ) && 43200 > ( time() - $r->last_checked );
		if ($time_not_changed) return;
		if ($r->new_version <= $SlimCfg->version) {
			$r->new_version = $SlimCfg->version;
			if ($update = $SlimCfg->version_check()) {
				$r->new_version = $update;
				$r->last_checked = time();
			}
		}
		update_option('wp_slimstat_ex_latest', $r);
		if ($r->new_version > $SlimCfg->version) {
			$current = get_option( 'update_plugins' );
			if (!isset($current->response[$file])) {
				$current->response[$file]->id = '999999999999';
				$current->response[$file]->slug = $r->slug;
				$current->response[$file]->new_version = $r->new_version;
				$current->response[$file]->url = $SlimCfg->plugin_home;
				$current->response[$file]->package = $SlimCfg->package_url;
				update_option('update_plugins', $current);
			}
		}
	}

}
// end of class wp_slimstat_ex

/* WP version check
-------------------------------------------*/
if (version_compare($SlimCfg->wp_version, '2.5', '<')) {
	add_action('admin_notices', array('wp_slimstat_ex', '_not_compatible'));
	return;// do not load wp-slimstat-ex any more
}

/* Setup, Option Page, Load CSS & Javascripts
-------------------------------------------*/
add_action('plugins_loaded', array(&$SlimCfg, 'plugins_loaded'));

// setup WP-SlimStat-Ex
add_action('activate_'.$SlimCfg->_basename(__FILE__), array('wp_slimstat_ex', 'setup'));

// Add SlimStat panel
add_action('admin_menu', array('wp_slimstat_ex', 'slimstat_menu'));

/* Upgrade from previous version */
if (!wp_slimstat_ex::check_current_version()) {
	define('SLIMSTAT_UPGRADE', true);
	add_action('admin_notices', array('wp_slimstat_ex', '_upgrade_notice'));
	return;
}

/* Includes
-------------------------------------------*/
if ( $SlimCfg->geoip != 'mysql' ) {
	if ( !function_exists('geoip_country_code_by_name') )
		require_once(SLIMSTATPATH . 'lib/geoip/geoipcity.inc');
	require_once(SLIMSTATPATH . 'lib/geoip/geoipregionvars.php');

	add_action( 'shutdown', array( &$SlimCfg, 'geoip_close' ), 25 );
}

require_once(SLIMSTATPATH . 'lib/functions.php');

// include all active pins
if ($SlimCfg->option['usepins']) {
	require(SLIMSTATPATH . 'lib/pins.php');
	$current_pins = SSPins::_getPins(1, 5);
	if (!empty($current_pins)) {
		foreach((array)$current_pins as $current_pin) {
			$current_pin_file = SLIMSTAT_PINPATH . $current_pin->name . '/pin.php';
			if ('' != $current_pin->name && file_exists($current_pin_file)) {
				include_once($current_pin_file);
				SSPins::register_pin($current_pin);
			}
		}
	}
}

/* Call queried object (Fix for http://trac.wordpress.org/ticket/5121)
-------------------------------------------*/
add_action('wp', array('wp_slimstat_ex', 'call_queried_object'), 0);

/* Plugin update check
-------------------------------------------*/
add_action( 'after_plugin_row', array('wp_slimstat_ex', 'plugin_update_row' ), 0 );


/* Track visitors
-------------------------------------------*/
require_once(SLIMSTATPATH . 'lib/track.php');
if (!$SlimCfg->option['track_mode'] || $SlimCfg->option['track_mode'] == 'full') {
	// Disable track when redirecting
	add_filter('wp_redirect', array(&$SSTrack, 'remove_shutdown_hooks'));
	add_action('shutdown', array(&$SSTrack, 'slimtrack'));
} else {
	add_action('wp_footer', array(&$SSTrack, 'slimtrack'), 9999);
	if ( $SlimCfg->option['track_mode'] == 'footer_feed' ) {
		// Disable track when redirecting
		add_filter('wp_redirect', array(&$SSTrack, 'remove_shutdown_hooks'));
		add_action('template_redirect', array(&$SSTrack, 'feed_track_footer'));
	}
}
?>