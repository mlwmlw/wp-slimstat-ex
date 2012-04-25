<?php // Powered by wordpress install tool.
require_once('_load_wp.php');
nocache_headers();
$SlimCfg->check_user();

require_once('_functions.php');
if(!isset($ssAdmin))
	$ssAdmin =& SSAdmin::get_instance();

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;
$_go = $ssAdmin->_go();

$header_title = !empty($header_title) ? trim($header_title) : 'Admin';
$header_title = __('Wp-SlimStat-Ex &rsaquo; '.$header_title, 'slimstat-admin');
header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $header_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php wp_admin_css('css/colors-fresh', true); ?>
	<?php wp_admin_css('css/install', true); ?>
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />

</head>
<body>
<h1 id="logo"><img src="slimstat-logo.png" alt="slimstat" /></h1>
