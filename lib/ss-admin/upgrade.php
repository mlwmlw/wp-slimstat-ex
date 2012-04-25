<?php
$header_title = 'Upgrade';
include('_header.php');
require_once(SLIMSTATPATH . 'lib/upgrade.php');
// Let's check to make sure ShortStat table exists.
switch($step) {
/*_________________________________CASE 0 */
case 0:
	$version_check = (get_option('wp_slimstat_ex_version') == $ssAdmin->version);
?>
	<h1><?php _e('Welcome to SlimStat Upgrade tool.', 'slimstat-admin'); ?></h1>
<?php if($version_check) { ?>
	<h3><?php _e('You do not need to upgrade SlimStat', 'slimstat-admin'); ?></h3>
	<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php } else { ?>
	<h3><?php _e('Before we start', 'slimstat-admin'); ?></h3>
	<ul>
	<li><?php _e('Upgrade process may takes time depend on your DB size.', 'slimstat-admin'); ?></li>
	<li><?php _e('Please be patient for full upgrade process.', 'slimstat-admin'); ?></li>
	</ul>
	<h2 class="step"><a href="upgrade.php?step=1"><?php _e('Do Upgrade', 'slimstat-admin'); ?> &raquo;</a></h2>

<?php }
break;
/*_________________________________CASE 1 */
case 1:
?>
	<h1><?php _e('Upgrade Steps', 'slimstat-admin'); ?></h1>
	<ul>
	<li><?php _e('There is several sub-steps while upgrading SlimStat-Ex', 'slimstat-admin'); ?></li>
	<li><?php _e('Each step may takes times depend on your DB size', 'slimstat-admin'); ?></li>
	<li><?php _e('Please be patient until each step is fully done', 'slimstat-admin'); ?></li>
	<li><?php _e('If you can not see the "Ok, done" message (end of this page), <br />click browser\'s back button and "Do This Step" until you see the message', 'slimstat-admin'); ?></li>
	</ul>
<?php
	$_upgrade = SSUpgrade::do_upgrade($step);
	$current_version = get_option('wp_slimstat_ex_version');
	if(!$current_version) $current_version = '1.x';

	if(is_array($_upgrade)) {
?>
	<p><?php printf(__('You\'re on the progress for upgrading SlimStat-Ex v<em>%1$s</em> to v<em>%2$s</em>', 'slimstat-admin'), $current_version, $ssAdmin->version); ?></p>
<?php
	} else {
		if(!$_upgrade) {
			$head_msg = __('Failed to upgrade SlimStat', 'slimstat-admin');
			$message = __('Your SlimStat-Ex was not upgraded properly.');
			$back_to_url = 'upgrade.php?step=1';
			$back_to_msg = __('Do Over Again', 'slimstat-admin');
			$_class = 'error';
		} else {
			$head_msg = __('Upgrade Complete', 'slimstat-admin');
			$message = __('Your SlimStat-Ex has been successfully upgraded!');
			$back_to_url = get_option('siteurl').'/wp-admin/'.$SlimCfg->option_page;
			$back_to_msg = __('Back to SlimStat Options', 'slimstat-admin');
			$_class = 'updated';
		}
?>
	<h1><?php echo $head_msg ?></h1>
	<div class="<?php echo $_class; ?> fade"><p><?php echo $message; ?></p></div>
	<h2 class="step"><a href="<?php echo $back_to_url; ?>"><?php echo $back_to_msg; ?> &raquo;</a></h2>
<?php
	}
break;
}

include ('_footer.php');
?>