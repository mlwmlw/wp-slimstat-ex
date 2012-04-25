<?php // Powered by wordpress install tool.
include('_header.php');
?>
<h1><?php _e('Wp-SlimStat-Ex Admin Tool', 'slimstat-admin'); ?></h1>
<div class="updated fade"><p><?php _e('First of all, go to "<code>wp-admin &gt; SlimStat &gt; Global Options</code>" <span class="red bold">disable</span> tracking option', 'slimstat-admin'); ?></p></div>
<h3><a href="admin.php"><?php _e('Delete Old Database', 'slimstat-admin'); ?></a></h3>
<h3><a href="performance.php"><?php _e('SlimStat Performance Tool', 'slimstat-admin'); ?></a></h3>
<?php if ($SlimCfg->geoip == 'mysql') { ?>
<h3><a href="iptc.php"><?php _e('Update ip-to-country database', 'slimstat-admin'); ?></a></h3>
<?php } ?><?php /* does not supports upgrade from shortstat or slimstat anymore ?>
<h3><a href="slim2ex.php"><?php _e('Upgrade From Wp-SlimStat(0.92)', 'slimstat-admin'); ?></a></h3>
<h3><a href="short2slim.php"><?php _e('Upgrade From Wp-ShortStat', 'slimstat-admin'); ?></a></h3><?php */ ?>
<h3><a href="modulelist.php"><?php _e('Display available modules', 'slimstat-admin'); ?></a></h3>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat option page', 'slimstat-admin'); ?> &raquo;</a></h2>

<?php
include ('_footer.php');
?>