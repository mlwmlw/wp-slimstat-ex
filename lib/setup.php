<?php

class SSSetup {

	function do_setup() { 
		global $SlimCfg, $wpdb;

		wp_slimstat_ex::set_options();

		$isStatsCreated = SSSetup::_createSlimTable('stats');
		$isFeedCreated = SSSetup::_createSlimTable('feed');
		$isResourceCreated = SSSetup::_createSlimTable('resource');
		if ($isResourceCreated) {
			$wpdb->query("INSERT IGNORE INTO $SlimCfg->table_resource ( `id`, `rs_string`, `rs_md5`, `rs_title`, `rs_condition`) VALUES (1, '__localsearch__', MD5('__localsearch__'), '', '[search]')");
		}
		$isDtCreated = SSSetup::_createSlimTable('date');
		$isPinCreated = SSSetup::_createSlimTable('pins');
/*		$isSitesCreated = SSSetup::_createSlimTable('sites');
		if ($isSitesCreated) {
			$wpdb->query("INSERT IGNORE INTO $SlimCfg->table_sites (`domain`) VALUES ('')");
			$wpdb->query("UPDATE $SlimCfg->table_sites SET `id` = 0 WHERE `domain` = '' LIMIT 1");
			$wpdb->query("OPTIMIZE TABLE $SlimCfg->table_sites");
		}*/
		if (SLIMSTAT_USER_AGENT === true)
			$isUaCreated = SSSetup::_createSlimTable('ua');

		return wp_slimstat_ex::check_current_version(true);
	}// end setup

	function _createSlimTable($table = 'stats', $check_only = false) {
		global $wpdb, $SlimCfg;

		$charset_collate = '';

		if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}

		switch($table) {
			case 'date':
				$createTable = $SlimCfg->table_dt;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`dt_start` int(10) unsigned NOT NULL default 0,
					`dt_end` int(10) unsigned NOT NULL default 0,
					`hits` int(10) unsigned NOT NULL default 0,
					`visits` int(10) unsigned NOT NULL default 0,
					`uniques` int(10) unsigned NOT NULL default 0,
					`type` TINYINT unsigned NOT NULL default 0,
					KEY `dt_total` (`dt_start`, `dt_end`, `type`)
					)";
				break;
			case 'pins':
				$createTable = $SlimCfg->table_pins;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`name` tinytext NOT NULL,
					`title` tinytext NOT NULL default '',
					`author` tinytext NOT NULL,
					`url` varchar(200) NOT NULL default '',
					`text` varchar(255) NOT NULL default '',
					`modules` longtext NOT NULL,
					`version` varchar(15) NOT NULL default '',
					`active` int(1) NOT NULL default 0,
					`type` int(1) NOT NULL default 0,
					UNIQUE KEY `id` (`id`)
					) $charset_collate";
				break;
			case 'resource':
				$createTable = $SlimCfg->table_resource;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`rs_string` varchar(255) NOT NULL default '',
					`rs_md5`	 char(32) NOT NULL default '',
					`rs_title` varchar(255) NOT NULL default '',
					`rs_condition` varchar(40) NOT NULL default '',
					`site_id` tinyint(4) NOT NULL default 0,
					UNIQUE KEY `id` (`id`),
					UNIQUE KEY `rs_md5_site` (`rs_md5`,`site_id`),
					KEY `rs_title` (`rs_title`),
					KEY `rs_condition` (`rs_condition`)
					) $charset_collate PACK_KEYS=1";
			break;
/*			case 'sites':
				$createTable = $SlimCfg->table_sites;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(3) unsigned NOT NULL auto_increment,
					`domain` varchar(255) NOT NULL default '',
					UNIQUE KEY `id` (`id`)
					) $charset_collate PACK_KEYS=1";
			break;*/
			case 'ua':
				$createTable = $SlimCfg->table_ua;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`ua_string` varchar(255) NOT NULL default '',
					`ua_md5`	 char(32) NOT NULL default '',
					`browser` SMALLINT NOT NULL default -1,
					`platform` TINYINT NOT NULL default -1,
					UNIQUE KEY `id` (`id`),
					UNIQUE KEY `ua_md5` (`ua_md5`)
					) $charset_collate PACK_KEYS=1";
			break;
			case 'feed':
				$createTable = $SlimCfg->table_feed;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`remote_ip` int unsigned NOT NULL default 0,
					`language` varchar(5) NOT NULL default '',
					`country` varchar(2) NOT NULL default '',
					`domain` varchar(255) NOT NULL default '',
					`referer` varchar(255) NOT NULL default '',
					`searchterms` varchar(255) NOT NULL default '',
					`resource` INT(11) NOT NULL default 0,
					`user_agent` int(11) unsigned NOT NULL default 0,
					`platform` TINYINT NOT NULL default -1,
					`browser` SMALLINT NOT NULL default -1,
					`version` varchar(15) NOT NULL default '',
					`visit` int(10) unsigned NOT NULL default '0',
					`dt` int(10) unsigned NOT NULL default 0,
					UNIQUE KEY `id` (`id`),
					KEY `dt` (`dt`),
					KEY `remote_ip` (`remote_ip`),
					KEY `resource` (`resource`),
					KEY `referer` (`referer`),
					KEY `searchterms` (`searchterms`),
					KEY `country` (`country`)
					) $charset_collate PACK_KEYS=1 ";
				break;
			case 'stats': default:
				$createTable = $SlimCfg->table_stats;
				$myTableStatsQuery = "CREATE TABLE `$createTable` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`remote_ip` int unsigned NOT NULL default 0,
					`language` varchar(5) NOT NULL default '',
					`country` varchar(2) NOT NULL default '',
					`domain` varchar(255) NOT NULL default '',
					`referer` varchar(255) NOT NULL default '',
					`searchterms` varchar(255) NOT NULL default '',
					`resource` INT(11) NOT NULL default 0,
					`user_agent` int(11) unsigned NOT NULL default 0,
					`platform` TINYINT NOT NULL default -1,
					`browser` SMALLINT NOT NULL default -1,
					`version` varchar(15) NOT NULL default '',
					`visit` int(10) unsigned NOT NULL default '0',
					`dt` int(10) unsigned NOT NULL default '0',
					UNIQUE KEY `id` (`id`),
					KEY `dt` (`dt`),
					KEY `remote_ip` (`remote_ip`),
					KEY `resource` (`resource`),
					KEY `referer` (`referer`),
					KEY `searchterms` (`searchterms`),
					KEY `country` (`country`)
					) $charset_collate PACK_KEYS=1";
				break;
		}
		// This is the schema for data about visits.

		return SSSetup::maybe_create_table($createTable, $myTableStatsQuery, $check_only);
	}
	// end createSlimTable

	function maybe_create_table($createTable, $myTableStatsQuery, $check_only=false) {
		global $wpdb;
		if(!$check_only) {
			// Check if table is already there
			foreach ($wpdb->get_col("SHOW TABLES", 0) as $table ) {
				if ($table == $createTable) {
					return true;
				}
			}

			// Ok, let's proceed
			if ( $wpdb->query( $myTableStatsQuery ) === false ) {
				return false;
			}
		}
		// Just to be sure, we check that tables were actually created
		foreach ( $wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ( $table == $createTable ) {
				return true;
			}
		}
		return false;
	}

}


?>