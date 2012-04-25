<?php
// stripos() needed because stripos is only present on PHP 5
// borrowed from bad behaviour plugin (http://www.bad-behavior.ioerror.us/)
if (!function_exists('stripos')):
function stripos($haystack,$needle,$offset = 0) {
	return(strpos(strtolower($haystack),strtolower($needle),$offset));
}
endif;

// sort array by value of given key
// borrowed from comment on PHP manual (http://php.net/manual/en/function.uasort.php#52888)
if (!function_exists('__masort')):
function __masort(&$data, $sortby, $order='asc') {
	static $sort_funcs = array();
	$order = strtolower($order);

	if (empty($sort_funcs[$sortby])) {
		$code = "\$c=0;";
		foreach (split(',', $sortby) as $key) {
			$array = array_pop($data);
			array_push($data, $array);
			if ($order == 'asc') {
				if (is_numeric($array[$key]))
					$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) ) return \$c;";
				else
					$code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
			} else {
				if (is_numeric($array[$key]))
					$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] > \$b['$key']) ? -1 : 1 )) ) return \$c;";
				else
					$code .= "if ( (\$c = strcasecmp(\$b['$key']),\$a['$key']) != 0 ) return \$c;\n";
			}
		}
		$code .= 'return $c;';
		$sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	} else {
		$sort_func = $sort_funcs[$sortby];
	}
	$sort_func = $sort_funcs[$sortby];
	uasort($data, $sort_func);
}
endif;

?>