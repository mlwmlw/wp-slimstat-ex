<?php
include('_header.php');
?>
<h1><?php _e('Wp-SlimStat available modules', 'slimstat-admin'); ?></h1>
	<table class="widefat">
	<thead>
	<tr>
		<th><?php _e('ID', 'slimstat-admin'); ?></th>
		<th><?php _e('Dependency', 'slimstat-admin'); ?></th>
		<th><?php _e('Class', 'slimstat-admin'); ?></th>
		<th><?php _e('Name', 'slimstat-admin'); ?></th>
		<th><?php _e('Description', 'slimstat-admin'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th><?php _e('ID', 'slimstat-admin'); ?></th>
		<th><?php _e('Dependency', 'slimstat-admin'); ?></th>
		<th><?php _e('Class', 'slimstat-admin'); ?></th>
		<th><?php _e('Name', 'slimstat-admin'); ?></th>
		<th><?php _e('Description', 'slimstat-admin'); ?></th>
	</tr>
	</tfoot>
	<tbody id="plugins">
<?php 
$defaults = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,91,92);
$i = 0;
foreach($defaults as $dmo) {
?>
	<tr<?php if ($i % 2 != 0) echo ' class="alternate"'; ?>>
		<td><?php echo $dmo; ?></td>
		<td><?php _e('Core', 'slimstat-admin'); ?></td>
		<td>SSModule::</td>
		<td><?php echo SSFunction::id2module($dmo); ?></td>
		<td><?php echo SSFunction::get_title($dmo); ?></td>
	</tr>
<?php
	$i++;
}
if($SlimCfg->option['usepins']) {
//	$pins = $wpdb->get_results("SELECT id FROM $SlimCfg->table_pins WHERE active = 1");
	$pins = SSPins::_getPins(0,5);
	if($pins) {
		foreach($pins as $pin) {
			$mo_info = SSFunction::pin_mod_info($pin->id);
			$mo_info = $mo_info['modules'];
			foreach($mo_info as $n=>$info) {
				$moid = (($pin->id + 100)*100)+1+$n;
?>
		<tr<?php if ($i % 2 != 0) echo ' class="alternate"'; ?>>
			<td><?php echo $moid; ?></td>
			<td><?php echo $pin->title; ?></td>
			<td><?php echo "\${$pin->name}->"; ?></td>
			<td><?php echo $info['name']; ?></td>
			<td><?php echo $info['title']; ?></td>
		</tr>
<?php
				$i++;
			}
		}
	}
}
?>
	</tbody>
	</table>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat option page', 'slimstat-admin'); ?></a></h2>
<?php
include ('_footer.php');
?>