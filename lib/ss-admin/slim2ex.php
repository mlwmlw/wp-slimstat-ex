<?php
$header_title = 'Upgrade from v0.92';
include('_header.php');

switch($step) {
/*_________________________________CASE 0 */
	case 0:
	if (!$ssAdmin->_isStatTableDetected('oldslim')){
		echo '<h1>'.__('There is no previous SlimStat table.', 'slimstat-admin').'</h1>';
	} else {
?>
<p><?php _e('Welcome to SlimStat upgrade tool. (Upgrade from Wp-SlimStat 0.92) <br />If you want to upgrade from Wp-ShortStat, use <a href="short2slim.php">short2slim.php</a> file', 'slimstat-admin'); ?></p>
<?php
		if( !$_go ) {
	?>
<h3><?php _e('Before we start', 'slimstat-admin'); ?></h3>
<ul>
<li><?php _e('"<strong>disable</strong>" tracking option', 'slimstat-admin'); ?></li>
<li><?php _e('"<strong>Deactivate</strong>" your previous Wp-SlimStat(0.92)', 'slimstat-admin'); ?></li>
<li><?php _e('<span style="color:red;font-size:14px;font-weight:bold;">Backup</span> your', 'slimstat-admin');?> "<?php echo $ssAdmin->old_stats; ?>" <?php _e(' table. ', 'slimstat-admin'); ?></li>
<li><?php _e('Click "Check Tables &raquo;" to check Wp-SlimStat-Ex tables', 'slimstat-admin'); ?></li>
</ul>
<form action="slim2ex.php?step=0" method="post" id="step1done">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Check Tables', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
		} else { ?>
<?php 
			require(SLIMSTATPATH . 'lib/setup.php');
			$isTables = SSSetup::do_setup();
?>
<h2><?php _e('Ok, now go to next step', 'slimstat-admin'); ?></h2>
<p><?php _e('To Upgrade 0.92 to Wp-SlimStat-EX, click "Start Upgrade" button below', 'slimstat-admin'); ?></p>
<h2 style="color: blue;"><?php _e('This tool dose not support Uninstallation<br />(This cannot be undone)', 'slimstat-admin'); ?></h2>
	<h2 class="step"><a href="slim2ex.php?step=1"><?php _e('Start Upgrade', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
		}	
	}
	break;
/*_________________________________CASE 1 */
	case 1:
?>
<h1><?php _e('First Step', 'slimstat-admin'); ?></h1>
<h2><?php _e('Import previous SlimStat data', 'slimstat-admin'); ?></h2>
<?php
	if( !$_go ) {
		if($ssAdmin->option['tracking']) {
			$ssAdmin->option['tracking'] = 0;
			update_option('wp_slimstat_ex', $ssAdmin->option);
		}
	?>
<p><?php _e('Now, we will copy your previous SlimStat data to new SlimStat-Ex table', 'slimstat-admin'); ?></p>
<form action="slim2ex.php?step=1" method="post" id="step1done">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
	} else { ?>
<?php 
		$isTables = $ssAdmin->_importSlimStat();
?>
<h2><?php _e('Done.... ', 'slimstat-admin'); ?></h2>
<p><?php _e('Ok, now go to next step', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="slim2ex.php?step=2"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	}
	break;
/*_________________________________CASE 2 */
	case 2:
?>
<h1><?php _e('Second Step', 'slimstat-admin'); ?></h1>
<p><?php printf(__('Now import <em>resource data</em> to "%s" table', 'slimstat-admin'), $ssAdmin->table_resource); ?></p>
	<ul>
	<li><?php _e('There is several sub-steps while importing <em>resource data</em>', 'slimstat-admin'); ?></li>
	<li><?php _e('Each step may takes time depend on your DB size', 'slimstat-admin'); ?></li>
	<li><?php _e('Please be patient while each step is on progress', 'slimstat-admin'); ?></li>
	<li><?php _e('If you can not see the "Ok, done" message (end of this page), <br />click browser\'s back button and "Do This Step" until you see the message', 'slimstat-admin'); ?></li>
	</ul>
<?php
		$import = $ssAdmin->importResourceData($step);
	if(is_array($import)) {
?>
	<p><?php _e('You\'re on the progress for importing <em>resource data</em>', 'slimstat-admin'); ?></p>
<?php
	} else {
		if(!$import) {
			$head_msg = __('Failed to import <em>resource data</em>', 'slimstat-admin');
			$message = __('Your <em>resource data</em> was not imported properly.');
			$back_to_url = 'slim2ex.php?step=2';
			$back_to_msg = __('Do Over Again', 'slimstat-admin');
		} else {
			$head_msg = __('Import Complete', 'slimstat-admin');
			$message = __('Your <em>resource data</em> has been successfully imported!');
			$back_to_url = 'slim2ex.php?step=3';
			$back_to_msg = __('Next Step', 'slimstat-admin');
		}
?>
	<h1><?php echo $head_msg ?></h1>
	<p><?php echo $message; ?></p>
	<h2 class="step"><a href="<?php echo $back_to_url; ?>"><?php echo $back_to_msg; ?> &raquo;</a></h2>
<?php
	}
	break;
/*_________________________________CASE 2 */
	case 3:
?>
<h1><?php _e('Third Step', 'slimstat-admin'); ?></h1>
<p><?php printf(__('Now import <em>Feed stats</em> on "%1$s" to "%2$s"', 'slimstat-admin'), $ssAdmin->table_stats, $ssAdmin->table_feed); ?></p>
<?php 
	if (!isset($_POST['fdel'])) { 
?>
<form action="slim2ex.php?step=3" method="post">
<input type="hidden" name="fdel" value="no" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Import Feed Data', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php
	} else {
		if ($_POST['fdel'] == 'no') {
			$truncate = $wpdb->query("TRUNCATE TABLE $ssAdmin->table_feed");
			$q = "INSERT INTO $ssAdmin->table_feed 
				(remote_ip,language,country,domain,referer,searchterms,resource,platform,browser,version,visit,dt)
				SELECT ts.remote_ip,ts.language,ts.country,ts.domain,ts.referer,ts.searchterms,ts.resource,ts.platform,ts.browser,ts.version, ts.visit, ts.dt
				FROM $ssAdmin->table_stats ts, $ssAdmin->table_resource tr
				WHERE ts.resource = tr.id
				AND tr.rs_condition LIKE '[feed]%' ";
			if( false === $wpdb->query($q) ) {
?>
<p><?php _e('Failed to import <em>feed data</em>. Please do this step again.', 'slimstat-admin'); ?></p>
<form action="slim2ex.php?step=3" method="post">
<input type="hidden" name="fdel" value="no" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Do Over Again', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php } else { ?>
<p><?php _e('Data import success, now delete feed data from stats', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_stats; ?>)
<?php _e(' table.', 'slimstat-admin'); ?></p>
<p><?php _e('If any error occurred, click browser\'s back button and "Delete Feed Data &raquo;" again', 'slimstat-admin'); ?></p>
<form action="slim2ex.php?step=3" method="post">
<input type="hidden" id="fdel" name="fdel" value="yes" /> 
<p class="submit"><input type="submit" onclick="javascript:return(confirm('<?php printf(__('Are you sure to delete feed data in %s?', 'slimstat-admin'), $ssAdmin->table_stats); ?>'))" name="Submit" value="<?php _e('Delete Feed Data', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php } ?>
<?php } else if ( isset($_POST['fdel']) && $_POST['fdel'] == 'yes' ) {
	$query = "DELETE ts.* FROM $ssAdmin->table_stats ts, $ssAdmin->table_resource tr
				WHERE ts.resource = tr.id
				AND tr.rs_condition LIKE '[feed]%' ";
	if( false === $wpdb->query($query) ) {
?>
<p><?php _e('Failed to delete <em>feed data</em>. Please do this step again.', 'slimstat-admin'); ?></p>
<form action="slim2ex.php?step=3" method="post">
<input type="hidden" name="fdel" value="yes" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Do Over Again', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php } else { ?>

<h2><?php _e('Done.... ', 'slimstat-admin'); ?></h2>
<p><?php _e('Ok, now go to next step', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="slim2ex.php?step=4"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
			}
		}
	}
	break;
/*_________________________________CASE 3 */
	case 4:
?>
<h1><?php _e('Fourth Step', 'slimstat-admin'); ?></h1>
<p><?php _e('Now insert "visit" data. this may takes time depend on your stats DB size.', 'slimstat-admin'); ?></p>
<p><?php _e('If you can not see the "Next" button or MYSQL error occur during process, <br />click browser\'s back button and "Next 20000" again', 'slimstat-admin'); ?></p>
<p><?php _e('It takes about 40(sec) for every "Next 20000"', 'slimstat-admin'); ?></p>
<?php
	if( !$_go ) {
?>
<p><?php _e('This may takes time depend on your database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_stats; ?>) <?php _e('size', 'slimstat-admin'); ?><p>
<form action="slim2ex.php?step=4" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
	} else {
		if( isset($_POST['fvisit']) && $_POST['fvisit'] == 'go' ) 
			$ssAdmin->update_visit( $ssAdmin->table_feed );
		else $ssAdmin->update_visit( $ssAdmin->table_stats );
}
	break;
/*_________________________________CASE 4 */
	case 5:
?>
<h1><?php _e('5th Step', 'slimstat-admin'); ?></h1>
<p><?php _e('Now we will insert "dt" data to', 'slimstat-admin'); ?> <em><?php echo $ssAdmin->table_dt; ?></em>. 
<?php _e('this may takes time.', 'slimstat-admin'); ?></p>
<?php
	if( !$_go ) {
?>
<form action="slim2ex.php?step=5" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
	} else {
?>
<p style="color:red;font-size: 16px;"><?php _e('If you can not see the "Ok, done" message (end of this page), <br />click browser\'s back button and "Start This Step" until you see the message', 'slimstat-admin'); ?></p>
<?php 
		if(isset($_POST['fdt']) && $_POST['fdt'] == 'go') {
			$set_dt = $ssAdmin->set_dt_data('', 2);
			if($set_dt) echo '<h2 class="step"><a href="slim2ex.php?step=6">'.__('Next Step', 'slimstat-admin').' &raquo;</a></h2>';
		} else {
			$set_dt_nom = $ssAdmin->set_dt_data('', 1);
//			if($set_dt) {
?>
		<p><?php _e('Common stat data', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_stats; ?></em>) <?php _e('inserted to dt table.', 'slimstat-admin'); ?><p>
		<p><?php _e('Now, we will insert data from feed table', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_feed; ?></em>)</p>
		<form action="slim2ex.php?step=5" method="post">
		<input type="hidden" name="sstep" value="go" />
		<input type="hidden" name="fdt" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Get Feed Dt', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
<?php 
//			}
		}
	}
	break;
/*_________________________________CASE 5 */
	case 6:
		$query = "OPTIMIZE TABLE ".$ssAdmin->table_stats." ";
		if($wpdb->query($query) === false) {
			echo '<p>Failed to optimize table</p> ('.$ssAdmin->table_stats.')';
		}
		$query = "OPTIMIZE TABLE ".$ssAdmin->table_feed." ";
		if($wpdb->query($query) === false) {
			echo '<p>Failed to optimize table</p> ('.$ssAdmin->table_feed.')';
		}
		$query = "OPTIMIZE TABLE ".$ssAdmin->table_resource." ";
		if($wpdb->query($query) === false) {
			echo '<p>Failed to optimize table ('.$ssAdmin->table_resource.')</p>';
		}
?>
<h1><?php _e('Needed Steps Done', 'slimstat-admin'); ?></h1>
<p><em><?php echo $ssAdmin->table_stats.', '.$ssAdmin->table_feed.', '.$ssAdmin->table_resource; ?></em> <?php _e('optimized', 'slimstat-admin'); ?></p>
<p><?php _e('If you saw some errors, restore your backup data and start over this tool', 'slimstat-admin'); ?></p>
<p><?php _e('Now, go back to Slimstat option page and "<strong>enable</strong>" tracking option', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	break;
}
?>

<?php// } /*if slimstat table exists*/

include('_footer.php');
?>