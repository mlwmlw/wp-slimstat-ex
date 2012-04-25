<?php
/* External Track Config
---------------------------------------------------------------*/
// ** MySQL settings ** //
$slimtrack_ext['DB_NAME'] = 'databasename';    // The name of the database
$slimtrack_ext['DB_USER'] = 'username';     // Your MySQL username
$slimtrack_ext['DB_PASSWORD'] = 'password'; // ...and password
$slimtrack_ext['DB_HOST'] = 'localhost';    // 99% chance you won't need to change this value
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
if (!defined('DB_COLLATE')) define('DB_COLLATE', '');

// Change table_prefix to the same value as your wp-config.php file
$slimtrack_ext['table_prefix']  = 'wp_';   // Only numbers, letters, and underscores please!

if (!defined('SLIMSTAT_USER_AGENT'))
	define('SLIMSTAT_USER_AGENT', false); // set this true to save user-agent data.

?>