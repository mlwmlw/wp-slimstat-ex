<?php
$wp_root = preg_replace( '|wp-content/plugins.*$|','', str_replace('\\', '/', __FILE__) );
if (file_exists($wp_root . 'wp-load.php')) {
	require_once($wp_root . 'wp-load.php');
} else if (file_exists($wp_root . 'wp-config.php')) {
	require_once($wp_root . 'wp-config.php');
} else {
	// Die with an error message
	die("There doesn't seem to be a <code>wp-load.php(wp-config.php)</code> file.");
}
?>