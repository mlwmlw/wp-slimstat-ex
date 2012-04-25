<?php
/*
Module Name : Simple Stats
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL

Description ::
	You can manually display simple stats anywhere on your blog by inserting a line below to current theme template file.
		:: <?php if(function_exists('print_simple_hvu')) print_simple_hvu($wraper, $class); ?&gt;  ( replace &gt; with > )
		:: $wraper => if "div", output will be <div class="$class">__HTML__</div>. default = li
		:: $class => if "my-class", output will be <li class="my-class">__HTML__</li>. default = simple-stats
*/

if(isset($_GET['cssload']) && $_GET['cssload'] == '1') { // css file
header('Content-Type: text/css; charset: UTF-8');
?>
/* Simple Stats on wp_meta 
------------------------------------------*/
li.simple-stats {
list-style-type: none !important;
list-style-image: none !important;
}
.simple-stats {
font-size: 11px;
line-height: 1.2em;
text-align: center;
background-color: #efefef !important;
color: #444;
}
li.simple-stats .stats-total {
font-weight: bold;
}
li.simple-stats .stats-today {
}
li.simple-stats .stats-yesterday {
}
<?php 
	exit();
}

/* DO NOT EDIT BELOW LINES */
if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSSimpleStats extends SSPins { // Just a dummy class
	// About this Pin
	var $Pinfo = array(
		'title' => 'Simple Stats',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Display simple stats - maybe on sidebar',
		'version' => '0.2',
		'type' => 1,
	);

	// About displayable modules of this Pin
	var $Moinfo = array();

	function SSSimpleStats() {
		/* nothing */
	}
	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '1.6') {
			return array	('compatible' => false, 'message' => 'Simple Stats is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function pin_actions() {
		return array( 'options' => 1, 'extra_table' => 0 );
	}

	function pin_update_options() {
		if(!isset($_POST['simple_stats']) || !is_array($_POST['simple_stats']))
			return;
		$int = array('use_meta');
		$ops = $_POST['simple_stats'];
		foreach($ops as $k=>$v) {
			if(in_array($k, $int))
				$ops[$k] = (int)$v;
			else
				$ops[$k] = stripslashes(trim($v));
		}
		$this->update_option('simple_stats', $ops);
	}

	function pin_options() {
		$op = $this->get_option('simple_stats');
?>
<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table"> 
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Count Type:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Select count type to display', 'wp-slimstat-ex'); ?><br />
		<select name="simple_stats[count_type]">
			<option value="hits"<?php if($op['count_type'] == 'hits') { ?> selected="selected"<?php } ?>>hits</option>
			<option value="visits"<?php if($op['count_type'] == 'visits') { ?> selected="selected"<?php } ?>>visits</option>
			<option value="unique"<?php if($op['count_type'] == 'unique') { ?> selected="selected"<?php } ?>>unique</option>
		</select></td>
</tr>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Stats Type:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Select stats type to display', 'wp-slimstat-ex'); ?><br />
		<select name="simple_stats[stats_type]">
			<option value="all"<?php if($op['stats_type'] == 'all') { ?> selected="selected"<?php } ?>>All</option>
			<option value="common"<?php if($op['stats_type'] == 'common') { ?> selected="selected"<?php } ?>>Common</option>
			<option value="feed"<?php if($op['stats_type'] == 'feed') { ?> selected="selected"<?php } ?>>Feed</option>
		</select></td>
</tr>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Display on meta:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Automatically display simple stats on wp_meta()', 'wp-slimstat-ex'); ?><br />
	<?php _e('You can manually insert &lt;?php print_simple_hvu(); ?&gt; on your template file', 'wp-slimstat-ex'); ?><br />
		<select name="simple_stats[use_meta]">
			<option value="0"<?php if(!$op['use_meta']) { ?> selected="selected"<?php } ?>>NO</option>
			<option value="1"<?php if($op['use_meta']) { ?> selected="selected"<?php } ?>>YES</option>
		</select></td>
</tr>
</table>
<?php
	}

}//end of class

function wpss_blog_hvu( $period=0, $type="all" ) {
	global $SlimCfg;
	$hvu = array('hits'=>0, 'visits'=>0, 'uniques'=>0);
	switch($period) {
		case 0://today
			$hvu = SSFunction::calc_hvu( $SlimCfg->midnight_db, 0, $type );
		break;
		case 1://yesterday
			$dt_start_db = ($SlimCfg->midnight_db - 86400);
			$dt_end = $SlimCfg->midnight_db - 1;
			$hvu = SSFunction::calc_hvu( $dt_start_db, $dt_end, $type );
		break;
		case 2://this week
			$dt_start = $SlimCfg->midnight_print;
			$dt_end = $SlimCfg->midnight_db + 86399;
			while ( $SlimCfg->date( "w", $dt_start ) !=  1 ) { // move back to start of this week (1:Monday, 0:Sunday)
				$dt_start -= 86400;
			}
			$dt_start_db = $SlimCfg->time_switch($dt_start, 'db');
			if ($dt_end - $dt_start_db <= 0 ) 
				$dt_start_db = $SlimCfg->midnight_db;
			$hvu = SSFunction::calc_hvu( $dt_start_db, 0, $type );
		break;
		case 3://this month
			$dt_start_db = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0,'d'=>1), $SlimCfg->midnight_print, 'db');
			$hvu = SSFunction::calc_hvu( $dt_start_db, 0, $type );
		break;
		case 4://since first hit
			$hvu = SSFunction::calc_hvu( SSFunction::get_firsthit($type), 0, $type );
			$real_firsthit = SSFunction::get_real_firsthit($type);
			if( $real_firsthit ) {
				$hvu_del = SSFunction::deleted_hvu($type);
				$hvu['hits'] = $hvu['hits'] + $hvu_del['hits'];
				$hvu['visits'] = $hvu['visits'] + $hvu_del['visits'];
				$hvu['uniques'] = $hvu['uniques'] + $hvu_del['uniques'];
			}
		break;
		case 5://deleted
			$real_firsthit = SSFunction::get_real_firsthit($type);
			if( $real_firsthit )
				$hvu = SSFunction::deleted_hvu($type);
		break;
	}
	return $hvu;
}

function print_simple_hvu($wrap="li", $class='simple-stats') {
	global $SlimCfg;
	$default = array('count_type'=>'hits', 'stats_type'=>'all', 'use_meta'=>0);
	$option = SSPins::get_option('simple_stats');
	$option = array_merge($default, (array)$option);
	$wrap = trim(strtolower($wrap));
	$wraps = array('li', 'div', 'span', 'p', 'strong', 'b');
	if(!in_array($wrap, $wraps) || $wrap == "") $wrap = "li";
	$counts = $option['count_type'];
	$type = $option['stats_type'];
	$hvu_today = wpss_blog_hvu(0, $type);
	$hvu_yesterday = wpss_blog_hvu(1, $type);
//	$hvu_month = wpss_blog_hvu(2, $type);
	$hvu_sum = wpss_blog_hvu(4, $type);
	$today = $sum = $yesterday = 0;
	$output = "";
	if(in_array($counts, array('hits', 'visits', 'uniques'))) {
		$today = $hvu_today[$counts];
		$yesterday = $hvu_yesterday[$counts];
		$sum = $hvu_sum[$counts];
	}
	$output .= '<'.$wrap.' class="simple-stats"><span class="stats-total">'.__('Total', 'wp-slimstat-ex').' : '.$sum.'</span><br />';
	$output .= '<span class="stats-today">'.__('Today', 'wp-slimstat-ex').' : ' . $today. '</span> , ';
	$output .= '<span class="stats-yesterday">'.__('Yesterday', 'wp-slimstat-ex').' : ' . $yesterday. '</span>';
	$output .= '</'.$wrap.'>';
	echo $output;
}

function wpss_simple_hvu_css() {
	global $SlimCfg;
	echo '
	<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/pins/SSSimpleStats/pin.php?cssload=1" />'."\n";
}

add_action('wp_head', 'wpss_simple_hvu_css');

function print_simple_hvu_filter() {
	$option = SSPins::get_option('simple_stats');
	if($option['use_meta'])
		print_simple_hvu();
}

add_action('wp_meta', 'print_simple_hvu_filter');
?>