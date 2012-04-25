<?php
define('DOING_AJAX', true);

$_action = $_REQUEST['action'];

switch($_action) {
	case 'request_module':
	define('SLIMSTAT_MODULE_AJAX', true);
	break;
	case 'request_panel':
	define('SLIMSTAT_PANEL_AJAX', true);
	break;
	case 'request_chart':
	define('SLIMSTAT_CHART_AJAX', true);
	break;
	default: break;
}

include(dirname(__FILE__) . '/load-wp.php');

if (!defined('SLIMSTATPATH')) {die("Please activate Wp-SlimStat-Ex");}

//$content_type = $_action == 'request_chart' ? 'json' : 'html';
SSFunction::ajax_response_headers($content_type);

check_ajax_referer('slimstat-view-stats');

switch($_action):
case 'request_module': case 'request_chart':
	require_once(SLIMSTATPATH . 'lib/modules.php');
	$id = (int)$_GET["moid"]; 

	if ($id < 100) {
		if ( !$modle_fuc = SSFunction::id2module($id) )
			die('<p>There is no such module('.$id.')</p>');

		print call_user_func(array('SSModule', $modle_fuc));
		exit();
	} else {
		$pinid = floor($id/100) - 100;
		$mo = $id - (($pinid+100)*100) - 1;
		$mos = SSFunction::pin_mod_info($pinid);
		$pinName = $mos['name'];
		$file = SLIMSTAT_PINPATH . $pinName . '/pin.php';
		require_once(SLIMSTATPATH . 'lib/pins.php');
		require_once($file);
		global ${$pinName};
		if (!is_a(${$pinName}, 'SSPins'))
			${$pinName} = new $pinName();
		print call_user_func(array(&${$pinName}, $mos['modules'][$mo]['name']));
	}
	exit();
break;
case 'request_panel':
	require_once(SLIMSTATPATH . 'lib/display.php');
	// ignore offset on panel (module only)
	$SlimCfg->get['slim_offset'] = 0;
	SSDisplay::wp_slimstat_ajax_display(); 
	exit();
break;
break;
default:
	die(__('No ajax action provided', SLIMSTAT_DOMAIN));
break;
endswitch;
?>