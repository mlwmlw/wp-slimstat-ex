<?php
$header_title = 'Upgrade from Wp-ShortStat';
include('_header.php');

// Let's check to make sure ShortStat table exists.
$installed = $ssAdmin->_isStatTableDetected('shortstat');
if (!$installed) echo '<h1>'.__('We could not found WP-ShortStat table. (Did you renamed it?)', 'slimstat-admin').'</h1>';
else {
	switch($step) {
/*_________________________________CASE 0 */
		case 0:
		?>
		<p><?php _e('Welcome to SlimStat upgrade tool. (Upgrade from Wp-ShortStat) If you want to upgrade from Wp-SlimStat(0.92), use <a href="slim2ex.php">slim2ex.php</a> file', 'slimstat-admin'); ?></p>
		<h3><?php _e('Before we start', 'slimstat-admin'); ?></h3>
		<ul>
		<li><?php _e('Deactivate Wp-ShortStat if activated', 'slimstat-admin'); ?></li>
		<li><?php _e('<span style="color:red;font-size:14px;font-weight:bold;">Backup</span> your', 'slimstat-admin'); ?> "<?php echo $ssAdmin->table_shortstat; ?>" 
		<?php _e(' table. ', 'slimstat-admin'); ?></li>
		<li><?php _e('Delete or rename', 'slimstat-admin'); ?> "<?php echo $ssAdmin->table_stats; ?>" 
		<?php _e('if you already have one with stats data.', 'slimstat-admin'); ?></li>
		<li style="font-size: 15px;color:red;"><?php _e('This tool will EMPTY your', 'slimstat-admin'); ?> "<?php echo $ssAdmin->table_stats; ?>" 
		<?php _e('table if exists', 'slimstat-admin'); ?></li>
		<li><?php _e('This tool dose not support merged upgrade ("Wp-ShortStat" and "Wp-SlimStat"). You must select one.', 'slimstat-admin'); ?></li>
		</ul>
		<p><?php _e('To Upgrade Wp-ShortStat to Wp-SlimStat-EX, click "Start Upgrade" button below', 'slimstat-admin'); ?></p>
		<h2 style="color: blue;"><?php _e('This tool dose not support Uninstallation<br />(This cannot be undone)', 'slimstat-admin'); ?></h2>
			<h2 class="step"><a href="short2slim.php?step=1"><?php _e('Start Upgrade', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php
		break;
/*_________________________________CASE 1 */
		case 1:
		?>
		<h1><?php _e('First Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('First of all, we need to add some new tables and columns', 'slimstat-admin'); ?></p>
		<h2><?php _e('Create tables', 'slimstat-admin'); ?></h2>
		<?php 
		if( !$_go ) {
		?>
		<ul>
		<li><?php _e('create Feed table', 'slimstat-admin'); ?> (<?php echo $ssAdmin->table_feed; ?>) : 
		<?php _e('store feed(rss, atom) hits.', 'slimstat-admin'); ?></li>
		<li><?php _e('create Pins table', 'slimstat-admin'); ?> (<?php echo $ssAdmin->table_pins; ?>) : 
		<?php _e('needed for plugable panels like PathStats.', 'slimstat-admin'); ?></li>
		<li><?php _e('create dt table', 'slimstat-admin'); ?> (<?php echo $ssAdmin->table_dt; ?>) : 
		<?php _e('store hits, visits, uniques by time interval', 'slimstat-admin'); ?></li>
		</ul>
		<form action="short2slim.php?step=1" method="post" id="step1go">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php 
			} else { 
			require(SLIMSTATPATH . 'lib/setup.php');
			$isTables = SSSetup::do_setup();
			if($isTables !== false) {
		?>
		<h2><?php _e('Done.... ', 'slimstat-admin'); ?></h2>
		<p><?php _e('Ok, now go to next step', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=2"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php
			}
		}
		break;
/*_________________________________CASE 2 */
		case 2:
		?>
		<h1><?php _e('Second Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Before import ShorStat\'s data, we need to do something.', 'slimstat-admin'); ?></p>
		<?php
		if( !$_go ) {
			$find_id_index = $ssAdmin->_set_idindex();
			$reset_columns = $ssAdmin->_ss_reset_columns();
			$reset_index = $ssAdmin->_ss_reset_index();
		?>
		<p><?php _e('Fisrt, we will add some needed columns and index keys', 'slimstat-admin'); ?></p>
		<p><?php _e('Click "Start This Step &raquo;" button below', 'slimstat-admin'); ?></p>
		<form action="short2slim.php?step=2" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php 
		} else {
			$query = "ALTER TABLE $ssAdmin->table_shortstat
								ADD platform_slim TINYINT DEFAULT '-1' AFTER platform,
								ADD browser_slim SMALLINT DEFAULT '-1' AFTER browser,
								ADD country_slim varchar(2) default '' AFTER country,
								ADD INDEX browser_smartidx ( browser ( 10 ) ),
								ADD INDEX platform_smartidx ( platform ( 10 ) ),
								ADD INDEX country_smartidx ( country) ";

			if ( $wpdb->query( $query ) === false ) {
		?>
		<p><?php _e('maybe one or more columns are already exsits. have you done this step before?', 'slimstat-admin'); ?></p>
		<p><?php _e('If so, you can skip this step. go to next step', 'slimstat-admin'); ?></p>
		<?php
			}
		?>
		<h2><?php _e('Done... ', 'slimstat-admin'); ?></h2>
		<p><?php _e('Columns and index keys added, <br />now we will convert country, platform, browser data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=3"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php
		}
		break;
/*_________________________________CASE 3 */
		case 3:
		?>
		<h1><?php _e('Third Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Now, we will update country data (e.g. "Republic Of Korea" to "ko" )', 'slimstat-admin'); ?></p>
		<?php
		if ( !$_go ) { 
		?>
		<p><?php _e('If you can not see the "Next" button during process, click browser\'s back button and "Start This Step" again', 'slimstat-admin'); ?></p>
		<p><?php _e('This may takes time depend on your database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_shortstat; ?>) 
		<?php _e('size', 'slimstat-admin'); ?><p>
		<form action="short2slim.php?step=3" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php 
		} else {
			$limit = 50000;
			$dboffset = (isset($_POST["offset"]) && !empty($_POST["offset"])) ? (int)$_POST["offset"] : 0;
			if(isset($_POST['offset'])) {
				$start = ($dboffset-1)*$limit;
				$query = "SELECT id, country, country_slim FROM $ssAdmin->table_shortstat 
							WHERE country <> '' LIMIT ".$start.", ".$limit." ";
				if($rs = $wpdb->get_results($query)) {
					print '<span>converting</span><span class="dot">';
					foreach ($rs as $r) {
						if (empty($r->country_slim)) {
							$r->country = strtolower($r->country);
							if (isset($ssAdmin->country2code[$r->country])) {
								$query = "UPDATE ".$ssAdmin->table_shortstat." 
											SET country_slim = '".$ssAdmin->country2code[$r->country]."' 
											WHERE id = '".$r->id."' ";
								$wpdb->query($query);print '. ';
							}
						}
					} 
					print '</span><h2>Done, '.($dboffset*$limit).' lines converted till now</h2>';

					if(count($rs) !==0 && count($rs) < $limit) {
		?><h3><?php _e('Update Finished', 'slimstat-admin'); ?></h3> 
		<p><?php _e('country data converted, now we will convert platform data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=4"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
					}
				} else {
		?>
		<h3><?php _e('Data not found', 'slimstat-admin'); ?></h3>
		<p><?php _e('maybe you don\'t have country data and don\'t need to do this step', 'slimstat-admin'); ?></p>
		<p><?php _e('go to next step, we will convert platform data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=4"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
				}
			}
		?>
		<h3 style="color:red;"><?php _e('NOTE: Please wait until "Done" message appear', 'slimstat-admin'); ?></h3>
		<?php $message = ($dboffset == 0)?__('To start converting Click "Next ', 'slimstat-admin').$limit.__('" button', 'slimstat-admin'):__('Ok, now we will convert database ', 'slimstat-admin').($dboffset*$limit).' ~ '.(($dboffset+1)*$limit).__(' lines', 'slimstat-admin'); ?>
		<h4 style="color:green;"><?php echo $message; ?></h4>
		<form action="short2slim.php?step=3" method="post">
		<input type="hidden" name="offset" value="<?php echo $dboffset+1; ?>" />
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" value="<?php _e('Next', 'slimstat-admin'); ?> <?php echo $limit; ?>" /></p>
		</form>
		<?php
		}
		break;
/*_________________________________CASE 4 */
		case 4:
		?>
		<h1><?php _e('4th Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Now, we will update platform data (e.g. "Windows XP" to "1" )', 'slimstat-admin'); ?></p>
		<?php if ( !$_go ) { 
		?>
		<p><?php _e('If you can not see the "Next" button during process, click browser\'s back button and "Start This Step" again', 'slimstat-admin'); ?></p>
		<p><?php _e('This may takes time depend on your database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_shortstat; ?>) 
		<?php _e('size', 'slimstat-admin'); ?><p>
		<form action="short2slim.php?step=4" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php } else {
			$limit = 50000;
			$dboffset = (isset($_POST["offset"]) && !empty($_POST["offset"])) ? (int)$_POST["offset"] : 0;
			if(isset($_POST['offset'])) {
				$start = ($dboffset-1)*$limit;
				$query = "SELECT id, platform, platform_slim FROM ".$ssAdmin->table_shortstat." 
							WHERE platform <> '' LIMIT ".$start.", ".$limit." ";
				if($rs = $wpdb->get_results($query)) {
					print '<span>converting</span><span class="dot">';
					$i = 0;
					foreach ($rs as $r) {
						if ($r->platform_slim == '-1') {
							$r->platform = trim(strtolower($r->platform));
							if (isset($ssAdmin->platformString2ID[$r->platform])) {
								$query = "UPDATE ".$ssAdmin->table_shortstat." 
											SET platform_slim = '".$ssAdmin->platformString2ID[$r->platform]."' 
											WHERE id = '".$r->id."' ";
								$wpdb->query($query);print '. ';
							}
						}
					} 
					print '</span><h2>'.__('Done, ', 'slimstat-admin').($dboffset*$limit).__(' lines converted till now', 'slimstat-admin').'</h2>';

					if(count($rs) !==0 && count($rs) < $limit) {
		?><h3><?php _e('Update Finished', 'slimstat-admin'); ?></h3> 
		<p><?php _e('platform data converted, now we will convert browser data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=5"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
					}
				} else {
		?>
		<h3><?php _e('Data not found', 'slimstat-admin'); ?></h3>
		<p><?php _e('maybe you don\'t have platform data and don\'t need to do this step', 'slimstat-admin'); ?></p>
		<p><?php _e('go to next step, we will convert browser data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=5"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
				}
			}
		?>
		<h3 style="color:red;"><?php _e('NOTE: Please wait until "Done, ...." message appear', 'slimstat-admin'); ?></h3>
		<?php $message = ($dboffset == 0)?__('To start converting Click "Next ', 'slimstat-admin').$limit.__('" button', 'slimstat-admin'):__('Ok, now we will convert database ', 'slimstat-admin').($dboffset*$limit).' ~ '.(($dboffset+1)*$limit).__(' lines', 'slimstat-admin'); ?>
		<h4 style="color:green;"><?php echo $message; ?></h4>
		<form action="short2slim.php?step=4" method="post">
		<input type="hidden" name="offset" value="<?php echo $dboffset+1; ?>" />
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" value="Next <?php echo $limit; ?>" /></p>
		</form>
		<?php
		}
		break;
/*_________________________________CASE 5 */
		case 5:
		?>
		<h1><?php _e('5th Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Now, we will update browser data (e.g. "Firefox" to "4" )', 'slimstat-admin'); ?></p>
		<?php if ( !$_go ) { 
		?>
		<p><?php _e('If you can not see the "Next" button during process, click browser\'s back button and "Start This Step" again', 'slimstat-admin'); ?></p>
		<p><?php _e('This may takes time depend on your database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_shortstat; ?>) 
		<?php _e('size', 'slimstat-admin'); ?><p>
		<form action="short2slim.php?step=5" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php } else {
			$limit = 50000;
			$dboffset = (isset($_POST["offset"]) && !empty($_POST["offset"])) ? (int)$_POST["offset"] : 0;
			if(isset($_POST['offset'])) {
				$start = ($dboffset-1)*$limit;
				$query = "SELECT id, browser, browser_slim FROM ".$ssAdmin->table_shortstat." 
							WHERE browser <> '' LIMIT ".$start.", ".$limit." ";
				if($rs = $wpdb->get_results($query)) {
					print '<span>converting</span><span class="dot">';
					foreach ($rs as $r) {
						if ($r->browser_slim == '-1') {
							$r->browser = trim(strtolower($r->browser));
							if (isset($ssAdmin->browserString2ID[$r->browser])) {
								$query = "UPDATE ".$ssAdmin->table_shortstat." 
											SET browser_slim = '".$ssAdmin->browserString2ID[$r->browser]."' 
											WHERE id = '".$r->id."' ";
								$wpdb->query($query);print '. ';
							}
						}
					} 
					print '</span><h2>'.__('Done, ', 'slimstat-admin').($dboffset*$limit).__(' lines converted till now', 'slimstat-admin').'</h2>';

					if(count($rs) !==0 && count($rs) < $limit) {
		?><h3><?php _e('Update Finished', 'slimstat-admin'); ?></h3> 
		<p><?php _e('browser data converted, now we will import shortstat\'s data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=6"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
					}
				} else {
		?>
		<h3><?php _e('Data not found', 'slimstat-admin'); ?></h3>
		<p><?php _e('maybe you don\'t have browser data and don\'t need to do this step', 'slimstat-admin'); ?></p>
		<p><?php _e('go to next step, we will import shortstat\'s data', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=6"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<p id="footer"><a href="http://082net.com/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
				}
			}
		?>
		<h3 style="color:red;"><?php _e('NOTE: Please wait until "Done, ...." message appear', 'slimstat-admin'); ?></h3>
		<?php $message = ($dboffset == 0)?__('To start converting Click "Next ', 'slimstat-admin').$limit.__('" button', 'slimstat-admin'):__('Ok, now we will convert database ', 'slimstat-admin').($dboffset*$limit).' ~ '.(($dboffset+1)*$limit).__(' lines', 'slimstat-admin'); ?>
		<h4 style="color:green;"><?php echo $message; ?></h4>
		<form action="short2slim.php?step=5" method="post">
		<input type="hidden" name="offset" value="<?php echo $dboffset+1; ?>" />
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" value="<?php _e('Next', 'slimstat-admin'); ?> <?php echo $limit; ?>" /></p>
		</form>
		<?php
		}
		break;
/*_________________________________CASE 6 */
		case 6:
		?>
		<h1><?php _e('6th Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Now, we will import ShortStat\'s data', 'slimstat-admin'); ?></p>
		<?php if ( !$_go ) { 
			if($ssAdmin->option['tracking']) {
				$ssAdmin->option['tracking'] = 0;
				update_option('wp_slimstat_ex', $ssAdmin->option);
			}
		?>
		<p><?php _e('This may takes time depend on your database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_shortstat; ?>) 
		<?php _e('size', 'slimstat-admin'); ?><p>
		<form action="short2slim.php?step=6" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php 
		} else {
			$importSS = $ssAdmin->_importShortStat();
			if($importSS) {
		?>
		<h2><?php _e('.......Import Finished', 'slimstat-admin'); ?></h2> 
		<p><?php _e('Now we will optimize SlimStat table', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="short2slim.php?step=7"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php
			} else {
			echo __('<h2>There was some error with importing.</h2><p>please <a href="short2slim.php?step=6">do this step again</a></p>', 'slimstat-admin');
			}
		}
		break;
/*_________________________________CASE 7 */
		case 7:
		?>
		<h1><?php _e('7th Step', 'slimstat-admin'); ?></h1>
		<p><?php _e('Now, we need to optimize SlimStat\'s table', 'slimstat-admin'); ?></p>
		<?php if ( !$_go ) { 
		?>
		<p><?php _e('To optimize table click the "Start This Step &raquo;" below', 'slimstat-admin'); ?></p>
		<form action="short2slim.php?step=7" method="post">
		<input type="hidden" name="sstep" value="go" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
		</form>
		<?php 
		} else {
			$query = "OPTIMIZE TABLE ".$ssAdmin->table_stats." ";
			if($wpdb->query($query) === false) {
		?>
		<h2><?php _e('Failed to optimize table', 'slimstat-admin'); ?></h2>
		<p><?php _e('Please <a href="short2slim.php?step=7">do this step again</a>', 'slimstat-admin'); ?></p>
		<p id="footer"><a href="http://082net.com/tag/wp-slimstat-ex/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
		</body>
		</html>
		<?php return;
			}
		?>
		<h2><?php _e('Table Optimized', 'slimstat-admin'); ?></h2> 
		<p><?php _e('Now we\'ll go to SlimStat(0.92) upgrade tool\'s "Second Step"', 'slimstat-admin'); ?></p>
		<p><?php _e('We made your', 'slimstat-admin'); ?> "<?php echo $ssAdmin->table_stats; ?>" 
		<?php _e('table to Wp-SlimStat 0.92\'s condition<br />and remain steps are same as "slim2ex" steps', 'slimstat-admin'); ?></p>
		<h2 class="step"><a href="slim2ex.php?step=2"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
		<?php
		}
		break;
	}
}

include('_footer.php');
?>