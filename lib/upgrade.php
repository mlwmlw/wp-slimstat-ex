<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

if(!class_exists('SSAdmin'))
	require_once(SLIMSTATPATH . 'lib/ss-admin/_functions.php');

if(!isset($ssAdmin))
	$ssAdmin =& SSAdmin::get_instance();

class SSUpgrade {

	function SSUpgrade() {

	}

	function substep_form($done, $step, $substep, $_message, $_buttontext, $ver) {
		if(strlen($ver) == 1) $ver = $ver.'0';
		if($substep == 0) {
			$substep = 1;
			$message = sprintf($_message['first'], $ver{0}.'.'.substr($ver, 1));
			$buttontext = $_buttontext['first'];
		} elseif (!$done) {
			$message = sprintf($_message['fail'], $substep);
			$buttontext = $_buttontext['fail'];
		} else {
			$substep++;
			$message = sprintf($_message['ok'], $substep);
			$buttontext = $_buttontext['ok'];
		}
		$action = 'upgrade.php?step='.$step.'&amp;substep_'.$ver.'='.$substep;
		$class = ($substep%2) ? 'substep-alt':'substep';
?>
<div class="<?php echo $class;?>">
<h3><?php printf(__('Sub-Step %s', 'slimstat-admin'), $substep); ?></h3>
<form method="post" action="<?php echo $action; ?>" id="substep_form">
<ul><li><?php echo $message; ?></li></ul>
<p class="submit"><input type="submit" name="substep_submit" id="substep_submit" value="<?php echo $buttontext; ?>" /></p>
</form>
</div>
<script type="text/javascript">//<![CDATA[
function disable_substep_submit() {
	var substep_form = document.getElementById('substep_form');
	substep_form.onsubmit = function(){
		substep_form.substep_submit.disabled = true;
		substep_form.submit();
	};
}
var disableSubmitButton = disable_substep_submit();
//]]></script>
<?php
	}

	function message_prefix($message, $prefix='') {
		if (!is_array($message))
			return $prefix . $message;
		foreach ($message as $k=>$v)
			$message[$k] = $prefix . $message[$k];
		return $message;
	}

	function remove_unuesed_indexkeys_before_16_1() {
		global $ssAdmin;
		$upgrade = $ssAdmin->maybe_remove_indexkey('dt_total', 'common');
		if(!$upgrade) return false;
		$upgrade = $ssAdmin->maybe_remove_indexkey('dt_total', 'feed');
		if(!$upgrade) return false;
		$upgrade = $ssAdmin->maybe_remove_indexkey('resource_total', 'common');
		return $upgrade; 
	}

	function remove_unuesed_indexkeys_before_16_2() {
		global $ssAdmin;
		$upgrade = $ssAdmin->maybe_remove_indexkey('resource_total', 'feed');
		return $upgrade;
	}

	function add_indexkey_to_stat_table($key) {
		global $ssAdmin;
		$upgrade = $ssAdmin->maybe_add_indexkey($key,'common');
		if(!$upgrade) return false;
		$upgrade = $ssAdmin->maybe_add_indexkey($key,'feed');
		return $upgrade;
	}

	function createResourceTable() {
		require_once(SLIMSTATPATH . 'lib/setup.php');
		return SSSetup::_createSlimTable('resource');
	}

	function insertLocalSearchResource() {
		global $SlimCfg, $wpdb;
		$query1 = "UPDATE $SlimCfg->table_stats SET rs_id = 1 WHERE resource LIKE '__localsearch__' ";
		$query2 = "UPDATE $SlimCfg->table_feed SET rs_id = 1 WHERE resource LIKE '__localsearch__' ";
		$query3 = "INSERT IGNORE INTO $SlimCfg->table_resource ( `id`, `rs_string`, `rs_title`, `rs_condition`) VALUES (1, '__localsearch__', '', '[search]')";
		if($wpdb->query($query1) === false)
			return false;
		if($wpdb->query($query2) === false)
			return false;
		if($wpdb->query($query3) === false)
			return false;
		return true;
	}

	function insertOldResources() {
		global $SlimCfg, $wpdb;
		$query1 = "INSERT IGNORE INTO $SlimCfg->table_resource (`rs_string`)
			SELECT resource FROM $SlimCfg->table_stats
			WHERE rs_id <> 1
			GROUP BY resource";
		$query2 = "INSERT IGNORE INTO $SlimCfg->table_resource (`rs_string`)
			SELECT resource FROM $SlimCfg->table_feed
			WHERE rs_id <> 1
			GROUP BY resource";
		if($wpdb->query($query1) === false)
			return false;
		if($wpdb->query($query2) === false)
			return false;
		return true;
	}

	function updateResourceData() {
		global $SlimCfg, $wpdb;
		$query1 = "UPDATE $SlimCfg->table_stats ts, $SlimCfg->table_resource tr SET ts.rs_id = tr.id
				WHERE ts.resource=tr.rs_string
				AND ts.rs_id = '0'
				AND ts.rs_id <> '1' ";
		$query2 = "UPDATE $SlimCfg->table_feed ts, $SlimCfg->table_resource tr SET ts.rs_id = tr.id
				WHERE ts.resource=tr.rs_string
				AND ts.rs_id = '0'
				AND ts.rs_id <> '1' ";
		if($wpdb->query($query1) === false)
			return false;
		if($wpdb->query($query2) === false)
			return false;
		return true;
	}

	function updatePostTitleData() {
		global $SlimCfg, $wpdb;
		$query = "SELECT rs_string FROM $SlimCfg->table_resource WHERE rs_title LIKE '' AND rs_condition LIKE '' ";
		$unknown = $wpdb->get_results($query);
		if($unknown) {
			require_once(SLIMSTATPATH . 'lib/functions.php');
			$SlimCfg->option['guesstitle'] = 1;
			foreach($unknown as $r) {
				$_title = SSFunction::_guessPostTitle($r->rs_string);
				if(mysql_error($wpdb->dbh))
					return false;
			}
		}
		return true;
	}

	function removeResource_1() {
		global $SlimCfg, $wpdb, $ssAdmin;
		$query1 = "ALTER TABLE $SlimCfg->table_stats DROP COLUMN resource";
		$column1 = $ssAdmin->maybe_add_column($SlimCfg->table_stats, 'resource', '', true);
		if($column1 && $wpdb->query($query1) === false)
			return false;
		return true;
	}

	function removeResource_2() {
		global $SlimCfg, $wpdb, $ssAdmin;
		$query2 = "ALTER TABLE $SlimCfg->table_feed DROP COLUMN resource";
		$column2 = $ssAdmin->maybe_add_column($SlimCfg->table_feed, 'resource', '', true);
		if($column2 && $wpdb->query($query2) === false)
			return false;
		return true;
	}

	function renameRS_ID($table='common') {
		global $SlimCfg, $wpdb, $ssAdmin;
		$_table = ('feed' == $table) ? $SlimCfg->table_feed : $SlimCfg->table_stats;
		$query = "ALTER TABLE $_table CHANGE rs_id resource INT(11) NOT NULL DEFAULT 0";
		$column = $ssAdmin->maybe_add_column($_table, 'rs_id', '', true);
		if($column && $wpdb->query($query) === false)
			return false;
		return true;
	}

	function fix_domain_referer($table='common') {
		global $wpdb, $SlimCfg;
		$_table = ($table == 'feed') ? $SlimCfg->table_feed : $SlimCfg->table_stats;
		$query = "SELECT * FROM $_table ts WHERE ts.domain = '' AND ts.referer <> '' ";
		if($rows = $wpdb->get_results($query)) {
			foreach ($rows as $r) {
				$r->referer = preg_replace('|^http://|i', '', $r->referer);
				if(false === $uris = @parse_url('http://'.$r->referer)) {
					$r->domain = '';
					$r->referer = '';
				} else {
					if(!isset($uris['host'])) {
						$r->domain = '';
						$r->referer = '';
					} else {
						$r->domain = $uris['host'];
					}
				}
				$query2 = "UPDATE $_table ts SET ts.domain = '{$r->domain}', ts.referer = '{$r->referer}' WHERE ts.id = {$r->id} LIMIT 1";
				if(false === $wpdb->query($query2)) {
					return false;
				}
			}
		}
		return true;
	}

	function delete_uncompatible_pins_before16() {
		global $SlimCfg, $wpdb;
		require_once(SLIMSTATPATH . 'lib/pins.php');
		SSPins::findPins();
		if ( mysql_error($wpdb->dbh) )
			return false;
		return true;
	}

	function remove_duplicated_resource() {
		global $ssAdmin, $wpdb;
		$counts = SSUpgrade::get_duplicated_rs_counts();
		if ($counts === false)
			return false;
		while ($counts > 0) {
			$per = 500;
			$deps = SSUpgrade::get_duplicated_rs_data(0, $per);
			if (!$deps)
				return false;
			foreach ($deps as $r) {
				$_rs = '/'.trim($r->rs_string, '.:/');
				$_rs = $wpdb->escape($_rs);
				$query = "SELECT id FROM {$ssAdmin->table_resource} WHERE `rs_string` LIKE '{$_rs}' LIMIT 1";
				if ($ex = $wpdb->get_row($query)) {// if DB has no-trailing slashed resource
					$up_q = "UPDATE {$ssAdmin->table_stats} SET `resource` = '{$ex->id}' WHERE `resource` = '{$r->id}' ";
					$affected = (int)$wpdb->query($up_q);
					$up_q = "UPDATE {$ssAdmin->table_feed} SET `resource` = '{$ex->id}' WHERE `resource` = '{$r->id}' ";
					$affected += (int)$wpdb->query($up_q);
					$wpdb->query("UPDATE {$ssAdmin->table_resource} SET `dep` = 1 WHERE `id` = {$r->id}");// dep==1 ? remove
				} else {
					$up_q = "UPDATE {$ssAdmin->table_resource} SET `dep` = 2, `rs_string` = '{$_rs}' WHERE `id` = {$r->id}";// dep==2 ? remain
					$affected = $wpdb->query($up_q);
				}
//				if ($affected == 0)
//					echo 'affected rows : '. $affected. ' ('.$r->id.' - '. $r->rs_string.')<br />'. "\n";
			}
			$counts = SSUpgrade::get_duplicated_rs_counts();
			if ($counts === false)
				return false;
		}
		return true;
	}

	function get_duplicated_rs_data($offset, $limit) {
		global $wpdb, $ssAdmin;
		$offset = $offset * $limit;
		$query = "SELECT *, COUNT(rs_string) AS count_rs FROM {$ssAdmin->table_resource} 
		WHERE ( `rs_string` LIKE '%/' OR `rs_string` LIKE '//%' OR `rs_string` LIKE '/.%' )
		AND `rs_string` NOT LIKE '/'
		AND `dep` = 0
		GROUP BY rs_string
		ORDER BY count_rs DESC
		LIMIT $offset, $limit
		";
		if ($res = $wpdb->get_results($query))
			return $res;// object
		return false;
	}

	function get_duplicated_rs_counts() {
		global $wpdb, $ssAdmin;
		$query = "SELECT COUNT(*) AS count_rs FROM {$ssAdmin->table_resource} 
		WHERE ( `rs_string` LIKE '%/' OR `rs_string` LIKE '//%' OR `rs_string` LIKE '/.%' )
		AND `rs_string` NOT LIKE '/'
		AND `dep` = 0
		";
		if ($res = $wpdb->get_row($query))
			return $res->count_rs;
		return false;
	}

	function check_duplicated_rs_md5() {
		global $wpdb, $ssAdmin;
		$query = "SELECT tr.id, tr.rs_md5, COUNT(*) AS dupe_count FROM {$ssAdmin->table_resource} tr GROUP BY tr.rs_md5 HAVING dupe_count > 1 ORDER BY tr.id";
		$check = $wpdb->get_results($query);
		if (!$check)
			return true;
		foreach ($check as $d) {
			$query = "SELECT tr.id FROM {$ssAdmin->table_resource} WHERE  tr.rs_md5 LIKE '{$d->rs_md5}' AND tr.id <> {$d->id}";
			if ($res = $wpdb->get_results($query)) {
				$dupes = array();
				foreach ($res as $r) {
					$dupes[] = $r->id;
				}
				$_q1 = "UPDATE {$ssAdmin->table_stats} SET	`resource` = {$d->id} WHERE `resource` IN (".implode(', ', $dupes).")";
				$_q2 = "UPDATE {$ssAdmin->table_feed} SET	`resource` = {$d->id} WHERE `resource` IN (".implode(', ', $dupes).")";
				$up1 = $wpdb->query($_q1);
				if ($up1 === false)
					return false;
				$up2 = $wpdb->query($_q2);
				if ($up2 === false)
					return false;
			}
		}
		return true;
	}

	function fix_localsearch_resource() {
		global $SlimCfg, $ssAdmin, $wpdb;
		$row = $wpdb->get_row("SELECT * FROM $SlimCfg->table_resource WHERE id=1 LIMIT 1");
		if ( $row && '__localsearch__' == $row->rs_string )
			return true;
		$ls_id = $wpdb->get_row("SELECT id FROM $SlimCfg->table_resource WHERE rs_string = '__localsearch__' LIMIT 1");
		if ($row)
			$del = $wpdb->query("DELETE FROM $SlimCfg->table_resource WHERE id=1");
		if ($ls_id)
			$del = $wpdb->query("DELETE FROM $SlimCfg->table_resource WHERE rs_string= '__localsearch__' LIMIT 1");

			if ($ls_id) {
				$del = $wpdb->query("DELETE FROM $SlimCfg->table_resource WHERE rs_string= '__localsearch__' LIMIT 1");
				$up = $wpdb->query("UPDATE $SlimCfg->table_stats SET resource=1 WHERE resource={$ls_id}");
		}
		if ($to_fix) {
			$del = $wpdb->query("DELETE FROM $SlimCfg->table_resource WHERE rs_string = '__localsearch__' LIMIT 1");
		}
		$insert = $wpdb->query("INSERT IGNORE INTO $SlimCfg->table_resource ( `id`, `rs_string`, `rs_md5`, `rs_title`, `rs_condition`) VALUES (1, '__localsearch__', MD5('__localsearch__'), '', '[search]')");
	}

	function upgrade_from_before_16($step, $message, $buttontext) {
		global $SlimCfg, $ssAdmin;
		set_time_limit(180);
		$substep = (!isset($_GET['substep_16'])) ? 0 : (int)$_GET['substep_16'];
		switch($substep) {
			case 0:
				$done = true;
			break;
			case 1:
				$done = SSUpgrade::remove_unuesed_indexkeys_before_16_1();
			break;
			case 2:
				$done = SSUpgrade::remove_unuesed_indexkeys_before_16_2();
			break;
			case 3:
				$done = SSUpgrade::createResourceTable();
			break;
			case 4:
				$query = "ALTER TABLE $SlimCfg->table_stats ADD `rs_id` INT(11) NOT NULL DEFAULT 0 AFTER `resource`";
				$done = $ssAdmin->maybe_add_column($SlimCfg->table_stats, 'rs_id', $query);
			break;
			case 5:
				$query = "ALTER TABLE $SlimCfg->table_feed ADD `rs_id` INT(11) NOT NULL DEFAULT 0 AFTER `resource`";
				$done = $ssAdmin->maybe_add_column($SlimCfg->table_feed, 'rs_id', $query);
			break;
			case 6:
				$done = SSUpgrade::insertLocalSearchResource();
			break;
			case 7:
				$done = SSUpgrade::insertOldResources();
			break;
			case 8:
				$done = SSUpgrade::updateResourceData();
			break;
			case 9:
				$done = SSUpgrade::updatePostTitleData();
			break;
			case 10:
				$done = SSUpgrade::removeResource_1();
			break;
			case 11:
				$done = SSUpgrade::removeResource_2();
			break;
			case 12:
				$done = SSUpgrade::renameRS_ID('common');
			break;
			case 13:
				$done = SSUpgrade::renameRS_ID('feed');
			break;
			case 14:
				$done = $ssAdmin->maybe_add_indexkey('resource', 'common');
			break;
			case 15:
				$done = $ssAdmin->maybe_add_indexkey('resource', 'feed');
			break;
			case 16:
				$done = SSUpgrade::add_indexkey_to_stat_table('dt');
			break;
			case 17:
				$done = SSUpgrade::add_indexkey_to_stat_table('remote_ip');
			break;
			case 18:
				$done = SSUpgrade::add_indexkey_to_stat_table('referer');
			break;
			case 19:
				$done = SSUpgrade::add_indexkey_to_stat_table('searchterms');
			break;
			case 20:
				$done = SSUpgrade::add_indexkey_to_stat_table('country');
			break;
			case 21:
				$done = SSUpgrade::fix_domain_referer('common');
			break;
			case 22:
				$done = SSUpgrade::fix_domain_referer('feed');
			break;
			case 23:
				$done = $ssAdmin->OptimizeTables(array('common', 'feed', 'resource'));
			break;
			case 24:
				$done = SSUpgrade::delete_uncompatible_pins_before16();
			break;
			case 25:
				return true;
			break;
		}
		SSUpgrade::substep_form($done, $step, $substep, $message, $buttontext, '16');
		return array(1);
	}

	function upgrade_from_before_20($step, $message, $buttontext) {
		global $SlimCfg, $ssAdmin, $wpdb;
		set_time_limit(180);
		$substep = (!isset($_GET['substep_20'])) ? 0 : (int)$_GET['substep_20'];
		switch($substep) {
			case 0:
				$done = true;
			break;
			case 1:
				$message = SSUpgrade::message_prefix($message, "<p>Adding temporary column...</p>");
				$query = "ALTER TABLE {$ssAdmin->table_resource} ADD `dep` INT (1) NOT NULL default 0;";
				$done = $ssAdmin->maybe_add_column($ssAdmin->table_resource, 'dep', $query);
			break;
			case 2:
				$message = SSUpgrade::message_prefix($message, "<p>Removing unused index key(rs_string)...</p>");
				$done = $ssAdmin->maybe_remove_indexkey('rs_string', $ssAdmin->table_resource, 'UNIQUE');
				if ($done) {
					$update_table = $wpdb->query("UPDATE {$ssAdmin->table_resource} SET rs_string = TRIM(rs_string)");
					$done = $update_table !== false;
				}
			break;
			case 3:
				$message = SSUpgrade::message_prefix($message, "<p>Adding new column `rs_md5`...</p>");
				$query = "ALTER TABLE {$ssAdmin->table_resource} ADD `rs_md5` CHAR(32) NOT NULL default '' AFTER rs_string;";
				$done = $ssAdmin->maybe_add_column($ssAdmin->table_resource, 'rs_md5', $query);
			break;
			case 4:
				$message = SSUpgrade::message_prefix($message, "<p>Inserting new values for `rs_md5` column...</p>");
				$query = "UPDATE {$ssAdmin->table_resource} SET `rs_md5` = MD5(TRIM(`rs_string`)) WHERE `rs_md5` LIKE ''; ";
				$update_table = $wpdb->query($query);
				$done = $update_table !== false;
			break;
			case 5:
				$message = SSUpgrade::message_prefix($message, "<p>Checking duplicated `rs_md5` values...</p>");
				$done = SSUpgrade::check_duplicated_rs_md5();
			break;
			case 6:
				$message = SSUpgrade::message_prefix($message, "<p>Adding new unique index key (rs_md5)...</p>");
				$done = $ssAdmin->maybe_add_indexkey('rs_md5', $ssAdmin->table_resource, 'UNIQUE');
			break;
			case 7:
				$message = SSUpgrade::message_prefix($message, "<p>Optimizing table `{$ssAdmin->table_resource}`...</p>");
				$done = $ssAdmin->OptimizeTables(array('resource'));
			break;
			case 8:
				$message = SSUpgrade::message_prefix($message, "<p>Removing duplicated resources...</p>");
				$done = SSUpgrade::remove_duplicated_resource();
			break;
			case 9:
				$message = SSUpgrade::message_prefix($message, "<p>Deleting unused resources...</p>");
				$remove_rows = $wpdb->query("DELETE FROM {$ssAdmin->table_resource} WHERE `dep` = 1");
				$done = $remove_rows !== false;
			break;
			case 10:
				$message = SSUpgrade::message_prefix($message, "<p>Dropping temporary column `dep	`...</p>");
				$query = "ALTER TABLE $ssAdmin->table_resource DROP COLUMN dep";
				$col = $ssAdmin->maybe_add_column($ssAdmin->table_resource, 'dep', '', true);
				if($col && $wpdb->query($query) === false)
					$done = false;
				else
					$done = true;
			break;
			case 11:
				$message = SSUpgrade::message_prefix($message, "<p>Optimizing table `{$ssAdmin->table_resource}`...</p>");
				$done = $ssAdmin->OptimizeTables(array('resource'));
			break;
			case 12:
				return true;
			break;
		}
		SSUpgrade::substep_form($done, $step, $substep, $message, $buttontext, '20');
		return array(1);
	}

	function upgrade_from_before_21($step, $message, $buttontext) {
		global $SlimCfg, $ssAdmin, $wpdb;
		set_time_limit(180);
		$substep = (!isset($_GET['substep_21'])) ? 0 : (int)$_GET['substep_21'];
		switch($substep):
		case 0:
			$done = true;
		break;
		case 1:
			$message = SSUpgrade::message_prefix($message, "<p>Fixing local search resource...</p>");
			$done = SSUpgrade::fix_localsearch_resource();
		break;
		case 2:
			$message = SSUpgrade::message_prefix($message, "<p>Adding new column `site_id`...</p>");
			$query = "ALTER TABLE $SlimCfg->table_resource ADD `site_id` TINYINT(4) NOT NULL DEFAULT 0";
			$done = $ssAdmin->maybe_add_column($ssAdmin->table_resource, 'site_id', $query);
		break;
		case 3:
			$message = SSUpgrade::message_prefix($message, "<p>Removing old index `rs_md5`...</p>");
			$done = $ssAdmin->maybe_remove_indexkey('rs_md5', $ssAdmin->table_resource, 'UNIQUE');
		break;
		case 4:
			$message = SSUpgrade::message_prefix($message, "<p>Adding new index `rs_md5_site`...</p>");
			$done = $ssAdmin->maybe_add_indexkey('rs_md5_site', $ssAdmin->table_resource, 'UNIQUE');
		break;
		case 5:
			$message = SSUpgrade::message_prefix($message, "<p>Updating `resource` column type of feed stats table...</p>");
			$query = "ALTER TABLE `$ssAdmin->table_feed` CHANGE `resource` `resource` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
			$done = $ssAdmin->maybe_change_column($ssAdmin->table_feed, 'resource', array('Type'=>'int(11) unsigned'), $query, false);
		break;
		case 6:
			$message = SSUpgrade::message_prefix($message, "<p>Updating `resource` column type of common stats table...</p>");
			$query = "ALTER TABLE `$ssAdmin->table_stats` CHANGE `resource` `resource` INT(11) UNSIGNED NOT NULL DEFAULT '0'";
			$done = $ssAdmin->maybe_change_column($ssAdmin->table_stats, 'resource', array('Type'=>'int(11) unsigned'), $query, false);
		break;
		case 7:
			$message = SSUpgrade::message_prefix($message, "<p>Updating `type` column type of date table...<br />and more...</p>");
			$query = "ALTER TABLE `$ssAdmin->table_dt` CHANGE `type` `type` TINYINT UNSIGNED NOT NULL DEFAULT '0'";
			$done = $ssAdmin->maybe_change_column($ssAdmin->table_dt, 'type', array('Type'=>'tinyint(3) unsigned'), $query, false);
			if ($done) {
				$update = $wpdb->update($ssAdmin->table_stats, array('browser'=>'1044'), array('browser'=>'81'));
				$update = $wpdb->update($ssAdmin->table_feed, array('browser'=>'1044'), array('browser'=>'81'));
			}
		break;
		case 8:
			return true;
		break;
		endswitch;
		SSUpgrade::substep_form($done, $step, $substep, $message, $buttontext, '21');
		return array(1);
	}

	function update_deleted_hvu() {
		global $wpdb, $SlimCfg;
		$backups = $wpdb->get_results("SELECT * FROM $SlimCfg->table_dt WHERE type > 10");
		if(!$backups)
			return true;
		// if previously failed to update dt table, delete broken data.
		$delete = $wpdb->query("DELETE FROM $SlimCfg->table_dt WHERE type = 13");

		$common = $wpdb->get_results("SELECT * FROM $SlimCfg->table_dt 	WHERE type = 11");
		foreach($common as $c) {
			$query = "SELECT * FROM $SlimCfg->table_dt WHERE type=12 AND ";
			$query .= "dt_end < ".($c->dt_end + 3599)." AND dt_end > ".($c->dt_end - 3599)." LIMIT 1";
			$f = $wpdb->get_row($query);
			if(!$f) continue;
			$query = "INSERT INTO ".$SlimCfg->table_dt." ( dt_start, dt_end, hits, visits, uniques, type ) VALUES ( ";
			$query .= min($c->dt_start,$f->dt_start) .", ".max($c->dt_end,$f->dt_end).", ";
			$query .= ($c->hits + $f->hits).", ".($c->visits + $f->visits).", ".($c->uniques + $f->uniques).", 13 )";
			$insert = $wpdb->query($query);
		}
		return $insert;
	}

	function upgrade_from_before_15($step, $message, $buttontext) {
		global $SlimCfg, $ssAdmin;
		$substep = (!isset($_GET['substep_15'])) ? 0 : (int)$_GET['substep_15'];
		switch($substep) {
			case 0:
				// Update Options
				if(!empty($SlimCfg->option['ignore']) && empty($SlimCfg->exclude['ignore_ip'])) {
					$SlimCfg->exclude['ignore_ip'] = $SlimCfg->option['ignore'];
					$update = update_option('wp_slimstat_ex_exclude', $SlimCfg->exclude);
					if($update)
						unset($SlimCfg->option['ignore']);
				}
				$_unset = array('stats_type','blog_stats','blog_js','meta','sweet_tips','trackings');
				foreach ($_unset as $_us) {
					if(isset($SlimCfg->option[$_us])) 
						unset($SlimCfg->option[$_us]);
				}
				update_option('wp_slimstat_ex', $SlimCfg->option);
				$done = true;
			break;
			case 1:
				//Update Pin table
				$query = "ALTER TABLE `".$SlimCfg->table_pins."` ADD `type` int(1) NOT NULL default 0 AFTER `active`";
				$done = $ssAdmin->maybe_add_column($SlimCfg->table_pins, 'type', $query);
			break;
			case 2:
				//Update dt table
				$done = SSUpgrade::update_deleted_hvu();
			break;
			case 3:
				return true;
			break;
		}
		SSUpgrade::substep_form($done, $step, $substep, $message, $buttontext, '15');
		return array(1);
	}

	function do_upgrade($step=1) {
		global $SlimCfg;
		$current_version = get_option('wp_slimstat_ex_version');
		if(!$current_version) {
			$updated = wp_slimstat_ex::check_current_version(true);
			if($updated)
				return true;
			$current_version = get_option('wp_slimstat_ex_version');
		}
		if($current_version == $SlimCfg->version) {
			return true;
		}
		$upgrade = true;

		$message = array( 'first'=>__('Upgrade from before %s', 'slimstat-admin'), 'fail'=>__('Failed to operate this sub-step(%s)', 'slimstat-admin'), 'ok'=>'<p>Ok, done.</p>'.__('Now we will do the next sub-step(%s)') );

		$buttontext = array('first'=>__('Start This Sub-Step', 'slimstat-admin'), 'fail'=>__('Do Over Again', 'slimstat-admin'), 'ok'=>__('Do This Step', 'slimstat-admin') );

		if(version_compare($current_version, '1.5', '<')) {
			$upgrade = SSUpgrade::upgrade_from_before_15($step, $message, $buttontext);
			if(!$upgrade) return false;
			if(!is_array($upgrade))
				update_option('wp_slimstat_ex_version', '1.5');
		}
		if($upgrade && !is_array($upgrade) && version_compare($current_version, '1.6', '<')) {
			$upgrade = SSUpgrade::upgrade_from_before_16($step, $message, $buttontext);
			if(!$upgrade) return false;
			if(!is_array($upgrade))
				update_option('wp_slimstat_ex_version', '1.6');
		}
		if($upgrade && !is_array($upgrade) && version_compare($current_version, '2.0', '<')) {
			$upgrade = SSUpgrade::upgrade_from_before_20($step, $message, $buttontext);
			if(!$upgrade) return false;
			if(!is_array($upgrade))
				update_option('wp_slimstat_ex_version', '2.0');
		}
		if($upgrade && !is_array($upgrade) && version_compare($current_version, '2.1', '<')) {
			$upgrade = SSUpgrade::upgrade_from_before_21($step, $message, $buttontext);
			if (!$upgrade) return false;
			if (!is_array($upgrade))
				update_option('wp_slimstat_ex_version', '2.1');
		}
		if($upgrade && !is_array($upgrade))
			update_option('wp_slimstat_ex_version', $SlimCfg->version);

		return $upgrade;
	}

}

?>