<?php
if (!file_exists(dirname(__FILE__) . '/external-config.php')) {
	return;// do nothing, no error messages
}

define('SLIMSTATPATH',  dirname( dirname( __FILE__ ) ) . '/');

if ( !file_exists(SLIMSTATPATH . 'lib/external-config.php') || !file_exists(SLIMSTATPATH . 'lib/external-inc.php') ) {
	return;// if there's no config file or SLIMSTATPATH is not defined to correct path
}

// include config file
require (dirname(__FILE__) . '/external-config.php');

// say slimstat this is external tracking.
define('SLIMSTAT_EXTRACK', true);

// do track
require (SLIMSTATPATH.'lib/external-inc.php');
?>