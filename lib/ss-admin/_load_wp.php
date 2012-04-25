<?php
$wp_root = preg_replace( '|wp-content/plugins.*$|','', str_replace('\\', '/', __FILE__) );
if (is_file($wp_root . 'wp-load.php')) {
	$wp_config = $wp_root . 'wp-load.php';
} else {
	$wp_config = $wp_root . 'wp-config.php';
}
if (!file_exists($wp_config)) 
    die("There doesn't seem to be a <code>wp-config.php</code> file.");
if (!file_exists('_functions.php')) 
    die("There doesn't seem to be a <code>_functions.php</code> file.");

require_once($wp_config);
?>