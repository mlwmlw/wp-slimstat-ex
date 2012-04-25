<?php 
include('_header.php');

$max_age = $SlimCfg->midnight_db - ( 86400 * $SlimCfg->option['dbmaxage'] );
$max_age_blog = $SlimCfg->time_switch($max_age, 'blog');

switch($step) {
/*_________________________________CASE 0 */
	case 0:
		?>
<h1><?php _e('Wp-SlimStat Admin Tool', 'slimstat-admin'); ?></h1>
<p><?php _e('This tool helps you sum up old data and delete it', 'slimstat-admin'); ?></p>
<?php 
	if($SlimCfg->option['dbmaxage'] < 0 ) {
?>
<p><?php _e('You can not set "dbmaxage" to smaller than 0.', 'slimstat-admin'); ?><p>
<?php 
	} else if ($SlimCfg->option['dbmaxage'] == 0 ) {
?>
<p><?php _e('Set "<span style="color:blue;">DB max-age</span>" to a number greater than zero(0) on SlimStat option page', 'slimstat-admin'); ?></p>
<?php
	} else {
?>
<p><?php _e('If you want to delete all SlimStat data more than', 'slimstat-admin'); ?> <?php print $SlimCfg->option['dbmaxage']; ?> 
<?php _e(' days old 	( before', 'slimstat-admin'); ?> <?php print $SlimCfg->date( "j M Y", $max_age_blog ); ?>
<?php _e('), click the "Next Step" link below.', 'slimstat-admin'); ?></p>
<p><?php _e('<strong>This CANNOT be undone.</strong> It is recommended that you save a recently-generated SlimStat report for posterity.', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="admin.php?step=1"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php 
	}
	break;
/*_________________________________CASE 1 */
	case 1:
?>
<h1><?php _e('First Step', 'slimstat-admin'); ?></h1>
<p><?php _e('First we need to sum up SlimStat data more than', 'slimstat-admin'); ?> <?php print $SlimCfg->option['dbmaxage']; ?> 
<?php _e('days old', 'slimstat-admin'); ?></p>
<p><?php _e('This step will not delete your data.', 'slimstat-admin'); ?><p>
<p><?php _e('If you want to sum up data only, <strong>just do this step 1</strong> and close your browser or back to your blog', 'slimstat-admin'); ?></p>
<?php 
	if( !$_go ) {
		$optimize_stats = $wpdb->query("OPTIMIZE TABLE ".$ssAdmin->table_stats." ");
		$optimize_feed = $wpdb->query("OPTIMIZE TABLE ".$ssAdmin->table_feed." ");
?>
<p><?php _e('Ok, we optimized your SlimStat tables', 'slimstat-admin'); ?> (<?php echo $ssAdmin->table_stats.", ".$ssAdmin->table_feed; ?>)</p>
<p><?php _e('To start sum up your data, click the "Start This Step &raquo;" button below', 'slimstat-admin'); ?></p>
<form action="admin.php?step=1" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php
	} else {
?>
<div class="error fade"><p><?php _e('If you can not see the "<strong>Ok, done</strong>" message(end of this page), <br />click browser\'s back button and "Start This Step" until you see the message', 'slimstat-admin'); ?></p></div>
<?php 
		if(isset($_POST['adt']) && $_POST['adt'] == 'go') {
			$set_dt_all = $ssAdmin->set_dt_data($SlimCfg->option['dbmaxage'], 3);
			if($set_dt_all) echo '<h2 class="step"><a href="admin.php?step=2">'.__('Next Step', 'slimstat-admin').' &raquo;</a></h2>';
		} else	if(isset($_POST['fdt']) && $_POST['fdt'] == 'go') {
			$set_dt = $ssAdmin->set_dt_data($SlimCfg->option['dbmaxage'], 2);
?>
		<p><?php _e('Total stat data', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_stats; ?></em>) 
		<?php _e('inserted to dt table.', 'slimstat-admin'); ?><p>
		<p><?php _e('Now, we will insert total dt data', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_stats; ?> + <?php echo $ssAdmin->table_feed; ?></em>)</p>
		<form action="admin.php?step=1" method="post">
		<input type="hidden" name="sstep" value="go" />
		<input type="hidden" name="adt" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Insert Total Dt', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
<?php
		} else {
			$set_dt_nom = $ssAdmin->set_dt_data($SlimCfg->option['dbmaxage'], 1);
?>
		<p><?php _e('Normal stat data', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_stats; ?></em>) 
		<?php _e('inserted to dt table.', 'slimstat-admin'); ?><p>
		<p><?php _e('Now, we will insert data from feed table', 'slimstat-admin'); ?>(<em><?php echo $ssAdmin->table_feed; ?></em>)</p>
		<form action="admin.php?step=1" method="post">
		<input type="hidden" name="sstep" value="go" />
		<input type="hidden" name="fdt" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Insert Feed Dt', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
<?php 
		}
	}
	break;
/*_________________________________CASE 2 */
	case 2:
?>
<h1><?php _e('Second Step', 'slimstat-admin'); ?></h1>
<p><?php _e('Now we will save hits, visits, uniques data more than', 'slimstat-admin'); ?> <?php print $SlimCfg->option['dbmaxage']; ?> 
<?php _e('days old to', 'slimstat-admin'); ?> <?php echo $ssAdmin->table_dt; ?></p>
<p><?php _e('This step will not delete your data. But if you want to sum up data only, please <strong>DO NOT start this step</strong>.', 'slimstat-admin'); ?> 
[ <a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a> ]</p>

<p><u><?php _e('Once started, Wp-Slimstat will treat saved hits, visits, uniques as deleted', 'slimstat-admin'); ?></u></p>
<?php 
	if( !$_go ) {
?>
<p><?php _e('To save your summary, click the "Start This Step &raquo;" button below', 'slimstat-admin'); ?></p>
<form action="admin.php?step=2" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php
	} else {
		$first_hit = SSFunction::get_firsthit('common');
		$save_old_common = SSFunction::ins_dt($first_hit, $max_age, 11);
		$first_hit = SSFunction::get_firsthit('feed');
		$save_old_feed = SSFunction::ins_dt($first_hit, $max_age, 12);
		$first_hit = SSFunction::get_firsthit('all');
		$save_old_all = SSFunction::ins_dt($first_hit, $max_age, 13);
?>
<h2><?php _e('Old summary saved, now we will delete old data', 'slimstat-admin'); ?></h2>
<h2 class="step"><a href="admin.php?step=3"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	}
	break;
/*_________________________________CASE 3 */
	case 3:
?>
<h1><?php _e('Third Step', 'slimstat-admin'); ?></h1>
<p><?php _e('Now, we will delete old data.', 'slimstat-admin'); ?></p>
<p><?php _e('<strong>This CANNOT be undone.</strong> It is recommended that you save a recently-generated SlimStat report for posterity.', 'slimstat-admin'); ?></p>
<?php
	if( !$_go ) {
?>
<p><?php _e('To delete old data, click the "Start This Step &raquo;" button below', 'slimstat-admin'); ?></p>
<form action="admin.php?step=3" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
	} else {
?>
<p><?php _e('Old data more than', 'slimstat-admin'); ?> <?php print $SlimCfg->option['dbmaxage']; ?> 
<?php _e('days old will be deleted on this step', 'slimstat-admin'); ?></p>
<div class="error fade"><p><?php _e('If you can not see the "Delete Finished" message, click browser\'s back button and "Delete" again', 'slimstat-admin'); ?></p></div>
<?php
		if ( !isset( $_POST["confirm"] ) || $_POST["confirm"] != "kill" ) {
?>
	<form method="post" action="admin.php?step=3">
	<input type="hidden" name="sstep" value="go" />
	<p><?php _e('If you want to delete all SlimStat data more than', 'slimstat-admin'); ?> <?php print $SlimCfg->option['dbmaxage']; ?> 
	<?php _e(' days old ( before', 'slimstat-admin'); ?> <?php print $SlimCfg->date( "j M Y", $max_age_blog ); ?>), 
	<?php _e('you must confirm this decision below.', 'slimstat-admin'); ?></p>
	<p><label for="confirm">
	<input type="checkbox" id="confirm" name="confirm" value="kill" /> 
		<?php _e('I confirm deletion of all SlimStat data from before', 'slimstat-admin'); ?> <?php print $SlimCfg->date( "j M Y", $max_age_blog ); ?>
	</label></p>
	<p class="submit"><input type="submit" value="<?php _e('Delete', 'slimstat-admin'); ?>" /></p>
	</form>
<?php
		} else if ( isset($_POST["confirm"]) && $_POST["confirm"] == "kill" ) {
			$query = "DELETE FROM ".$ssAdmin->table_stats."";
			$query .= " WHERE dt<".$max_age;
			print $query;
			$do_delete = $wpdb->query( $query );
			if($do_delete === false) {
				print "<p>".__('Failed to delete old data(stats). Please', 'slimstat-admin')." <a href=\"admin.php?step=3\">".__('do this step again', 'slimstat-admin')."</a><p>";
			} else {
				print "<p>".(int)$do_delete." ".__('entries deleted from stats table.', 'slimstat-admin')."</p>\n";
			}
			$query = "DELETE FROM ".$ssAdmin->table_feed."";
			$query .= " WHERE dt<".$max_age;
			print $query;
			$do_delete = $wpdb->query( $query );
			if($do_delete === false) {
				print "<p>".__('Failed to delete old data(feed). Please', 'slimstat-admin')." <a href=\"admin.php?step=3\">".	__('do this step again', 'slimstat-admin')."</a><p>";
			} else {
				print "<p>".(int)$do_delete." ".__('entries deleted from stats table.', 'slimstat-admin')."</p>\n";
			}
?>
<h2><?php _e('Delete Finished', 'slimstat-admin'); ?><?php _e(', now we will remove unused resource data', 'slimstat-admin'); ?></h2>
<h2 class="step"><a href="admin.php?step=4"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
		}
	}
	break;
/*_________________________________CASE 4 */
	case 4:
?>
<h1><?php _e('Fourth Step', 'slimstat-admin'); ?></h1>
<p><?php _e('Now, we will delete useless resource data.', 'slimstat-admin'); ?></p>
<?php
	if( !$_go ) {
?>
<p><?php _e('To delete old data, click the "Start This Step &raquo;" button below', 'slimstat-admin'); ?></p>
<form action="admin.php?step=4" method="post">
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php 
	} else {
?>
<p><?php _e('Unused resource data will be removed from slimstat resouce table.', 'slimstat-admin'); ?></p>
<p><?php _e('You can <a href="admin.php?step=5">SKIP</a> this step, if you want to keep unused resource data for perfomance and don\'t care about DB space.', 'slimstat-admin'); ?></p>
<?php
		echo '<p class="blue bold">'.__('Connect to your phpmyadmin and execute query below by yourself', 'slimstat-admin').'</p>';
		$query = "DELETE tr FROM `{$ssAdmin->table_resource}` tr
			LEFT JOIN `{$ssAdmin->table_stats}` ts ON tr.id = ts.resource
			LEFT JOIN `{$ssAdmin->table_feed}` tf ON tr.id = tf.resource
			WHERE ts.resource IS NULL
			AND tf.resource IS NULL";
			echo '<blockquote class="green">'.str_replace("\n", "<br />", $query).'</blockquote>';
?>
<h2><?php _e('When you\'ve done, go to final step.', 'slimstat-admin'); ?></h2>
<h2 class="step"><a href="admin.php?step=5"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	}
	break;
/*_________________________________CASE 5 */
	case 5:
		$optimized = $ssAdmin->OptimizeTables(array('common', 'feed', 'resource'));
		if (!$optimized) {
			echo '<p>Failed to optimize tables</p>';
		}
?>
<h1><?php _e('All Steps Done', 'slimstat-admin'); ?></h1>
<p><?php _e('SlimStat tables optimized successfully', 'slimstat-admin'); ?></p>
<p><?php _e('If you saw some errors, restore your backup data and start over this tool', 'slimstat-admin'); ?></p>
<p><?php _e('Now, go back to option page and "<strong>enable</strong>" tracking', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	break;
}

include ('_footer.php');
?>