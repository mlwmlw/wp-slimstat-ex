<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

class SSModule {

	function _moduleSummary($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause(false);
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>false));

		$panel = $SlimCfg->get['pn'];
		switch ($panel) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$gmt_offset = get_option('gmt_offset') * 3600;

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<thead><tr>\n";
		$output .= "\t\t<th class=\"first\">".__('When', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"second\">".__('Hits', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"last\">".__(ucfirst($visit_type), SLIMSTAT_DOMAIN)."</th>\n";
		$output .= "\t</tr></thead><tbody>\n";

		$mnt_print = $SlimCfg->midnight_print;
		$mnt_db = $SlimCfg->midnight_db;
		
		// today
		$dt_end = ($mnt_db + 86399);
		$hvu = SSFunction::calc_hvu( $mnt_db, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$mnt_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Today', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// yesterday
		$dt_start_db = ($mnt_db - 86400);
		$dt_end = $mnt_db-1;
		$hvu = SSFunction::calc_hvu( $dt_start_db, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Yesterday', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// this week
		$dt_start = $mnt_print;
		$dt_end = ($mnt_db + 86399);
		while ( $SlimCfg->date( "w", $dt_start, false ) !=  1 ) { // move back to start of this week (1:Monday, 0:Sunday)
			$dt_start -= 86400;
		}
		$dt_start_db = $SlimCfg->time_switch($dt_start, 'db');
		if ($dt_end - $dt_start_db <= 0 ) $dt_start_db = $mnt_db;
		$hvu = SSFunction::calc_hvu( $dt_start_db, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('This week', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// last week
		$dt_end = $dt_start_db - 1;
		$dt_start_db = $dt_start_db - 604800;
		$hvu = SSFunction::calc_hvu( $dt_start_db, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Last week', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// this month
		$dt_start_db = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'd'=>1), $mnt_print, 'db');
		$hvu = SSFunction::calc_hvu( $dt_start_db, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('This month', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// last month
		$dt_end = $dt_start_db - 1;
		$dt_start_db = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'm'=>'-1', 'd'=>1), $mnt_print, 'db');
		$hvu = SSFunction::calc_hvu( $dt_start_db, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Last month', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// this year
		$dt_start_db = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'm'=>1, 'd'=>1), $mnt_print, 'db');
		$dt_end = $mnt_db + 86399;// end of today
		$hvu = SSFunction::calc_hvu( $dt_start_db, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('This year', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// last year
		$dt_end = $dt_start_db - 1;
		$dt_start_db = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'm'=>1, 'd'=>1, 'y'=>'-1'), $mnt_print, 'db');
		$hvu = SSFunction::calc_hvu( $dt_start_db, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start_db.'|'.$dt_end), true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Last year', SLIMSTAT_DOMAIN)." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// all
		$first_hit = SSFunction::get_firsthit($type);
		$first_hit_print = $SlimCfg->time_switch($first_hit, 'print');
		$hvu = SSFunction::calc_hvu( $first_hit, 0, $type, $filter_clause );
		$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
		$output .= "\t<tr class=\"".$class."\">\n";
		$output .= "\t\t<td class=\"first\">Since ";
		$output .= $SlimCfg->date( __('j M Y, H:i', SLIMSTAT_DOMAIN), $first_hit_print )."</td>";
		$output .= "<td class=\"second\">".$hvu['hits']."</td>";
		$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
		$output .= "\t</tr>\n";

		// deleted
		$real_firsthit = SSFunction::get_real_firsthit($type);
		if( $real_firsthit && ( empty($filter_clause) || $filter_clause == SLIMSTAT_DEFAULT_FILTER ) ) {
			$hvu = SSFunction::deleted_hvu($type);
			$output .= "\t<tr class=\"accent\">\n";
			$output .= "\t\t<td class=\"first\"><abbr title=\"".attribute_escape(__('Deleted stats', SLIMSTAT_DOMAIN))."\">".$SlimCfg->date(__('j M, Y', SLIMSTAT_DOMAIN), $SlimCfg->time_switch($real_firsthit, 'print') ).' ~ ';
			$output .= $SlimCfg->date( __('j M, Y', SLIMSTAT_DOMAIN), $first_hit_print-1 )."</abbr></td>";
			$output .= "<td class=\"second\"><del>".$hvu['hits']."</del></td>";
			$output .= "<td class=\"last\"><del>".$hvu[$visit_type]."</del></td>\n";
			$output .= "\t</tr>\n";
		}
		$output .= "\t</tbody></table>\n";

		return SSFunction::get_module_custom(__FUNCTION__, $output, $args);
	}
	
	function _moduleRecentReferers($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.resource, ts.referer, ts.domain, ts.dt 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('domain')."
			WHERE ts.referer <> ''
			AND ts.resource > 1
			AND ts.domain <> '' 
			AND ts.domain NOT IN (".SSFunction::my_domains('db').")
			AND $filter_clause
			ORDER BY ts.dt DESC LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Domain', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%3$s&amp;ff=0&amp;ft=0'));
		$cols[1]['html'] = '<a title="'.__('Visit this referer', SLIMSTAT_DOMAIN).'"%1$s>%2$s</a>'.$filter_btn;
		$cols[1]['formats'][0] = array('referer.encode_prefix', 'domain.short', 'domain.encode');

		$cols[2]['title'] = __('When', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('dt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}
	
	function _moduleRecentSearchStrings($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.resource, ts.referer, ts.searchterms, ts.dt 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('searchterms')."
			WHERE ts.searchterms <> '' 
				AND ts.resource > 1 
				AND $filter_clause
			ORDER BY ts.dt DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Search string', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%4$s&amp;ff=1&amp;ft=0'));
		$cols[1]['html'] = '<a%1$s title="%2$s">%3$s</a> '.$filter_btn;
		$cols[1]['formats'][0] = array('referer.encode_prefix', 'searchterms.long', 'searchterms.'.($args['style']=='wide'?'medium':'short'), 'searchterms.encode');

		$cols[2]['title'] = __('When', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('dt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}
	
	function _moduleNewDomains($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.referer, ts.resource, ts.domain, MIN(ts.dt) mindt
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('domain')."
			WHERE ts.domain NOT IN (".SSFunction::my_domains('db').")
				AND ts.domain <> ''
				AND ts.referer <> ''
				AND ts.resource NOT IN (0,1)
				AND $filter_clause
			GROUP BY ts.domain
			ORDER BY mindt DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Domain', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$cols[1]['html'] = '<a title="'.__('Visit this referer', SLIMSTAT_DOMAIN).'"%1$s>%2$s</a>';
		$cols[1]['formats'][0] = array('referer.encode_prefix', 'domain.'.($args['style']=='wide'?'long':'short'));

		$cols[2]['title'] = __('When', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('mindt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleRecentResources($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.resource, ts.dt 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('dt')."
			WHERE ts.resource NOT IN (0,1)
			AND $filter_clause
			ORDER BY ts.dt DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Resource', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=2&amp;ft=0'));
		$cols[1]['html'] = '%1$s  '.$filter_btn;
		$cols[1]['formats'][0] = array('resource.resource2title', 'resource.encode');

		$cols[2]['title'] = __('When', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('dt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}
	
	function _moduleLast24Hours($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause(false);
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>'force'));

		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];
		$offset = $SlimCfg->get['slim_offset'] * 86400;
//		if ( !isset($SlimCfg->get['fd']) || (isset($SlimCfg->get['fd']) && ($SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0]) <= 86400) ) {
			if( isset($SlimCfg->get['fd']) ) {
				$dt_end = $SlimCfg->time_switch($SlimCfg->get['fd'][0], 'print');
				$dt_end = $SlimCfg->mktime(array('i'=>59, 's'=>59), $dt_end, 'db');
				$dt_start = $SlimCfg->time_switch($SlimCfg->get['fd'][1], 'print');
				$dt_this_hour = $SlimCfg->mktime(array('i'=>59, 's'=>59), $dt_this_hour, 'db');
				$_dt_end = $dt_this_hour - 86399;
				$dt_end = $dt_end > $_dt_end ? $dt_end : $_dt_end;
			} else {
				$dt_start = $SlimCfg->time();
				$dt_this_hour = $SlimCfg->mktime(array('i'=>59, 's'=>59), $dt_start, 'db');
				$dt_end = $dt_this_hour - 86399;
			}
			$dt_this_hour -= $offset;
			$dt_end -= $offset;

			$output = "\n";
			$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$output .= "\t<thead><tr>\n";
			$output .= "\t\t<th class=\"first\">".__('Hour', SLIMSTAT_DOMAIN)."</th>";
			$output .= "<th class=\"second\">".__('Hits', SLIMSTAT_DOMAIN)."</th>";
			$output .= "<th class=\"last\">".__(ucfirst($visit_type), SLIMSTAT_DOMAIN)."</th>\n";
			$output .= "\t</tr></thead><tbody>\n";
				
			$i = 0; $c = 0;
			$chart_data = array();
			$chart_data[0]['legend'] = __('Hits', SLIMSTAT_DOMAIN);
			$chart_data[1]['legend'] = __(ucfirst($visit_type), SLIMSTAT_DOMAIN);
			$current_y = $current_m = $current_d = 0;

			for ($dt_start_db = $dt_this_hour; $dt_start_db > $dt_end; $dt_start_db -= 3600) {
				$hvu = SSFunction::calc_hvu( ( $dt_start_db - 3599 ), $dt_start_db, $type, $filter_clause );

				if ($SlimCfg->is_chart()) {
					$label_text = $SlimCfg->date_str( __("M j, H:00", SLIMSTAT_DOMAIN), $dt_start_db, &$current_y, &$current_m, &$current_d );
					$chart_data[0]['text'][$c] = $label_text;
				} else {
					$label_text = $SlimCfg->date_str( __("M j, H:00 - H:59", SLIMSTAT_DOMAIN), $dt_start_db, &$current_y, &$current_m, &$current_d );
				}

				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";

				if(max($hvu) > 0) {
					$filter_btn = SSFunction::get_filterBtns(array('fd'=>($dt_start_db-3599).'|'.$dt_start_db), true);
					$output .= "\t\t<td class=\"first center\">".$label_text." ".$filter_btn."</td>";
					$output .= "<td class=\"second\">".$hvu['hits']."</td>";
					$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";

					$chart_data[0]['onclick'][$c] = SSFunction::chart_onclick_url(array('fd'=>($dt_start_db-3599).'|'.$dt_start_db));
					$chart_data[0]['values'][$c] = (int)$hvu['hits'];
					$chart_data[1]['values'][$c] = (int)$hvu[$visit_type];
					$i++;
				} else {
					$output .= "\t\t<td class=\"first center\">".$label_text."</td>";
					$output .= "<td class=\"second\">0</td>";
					$output .= "<td class=\"last\">0</td>\n";

					$chart_data[0]['onclick'][$c] = false;
					$chart_data[0]['values'][$c] = $chart_data[1]['values'][$c] = 0;
				}
				$c++;
				$output .= "\t</tr>\n";
			}
			$output .= "\t</tbody></table>\n";

			$chart = array('type'=>'area', 'tip'=>'#val#<br>#key#');
			$chart['data'] = $chart_data;

			if ($i == 0) {
				$output = 0;
				$chart = false;
			}

//		} else
//			$output = 0;


		return SSFunction::get_module_custom(__FUNCTION__, $output, $args, $chart);
	}

	function _moduleDailyHits($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause(false);
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>'force'));

		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<thead><tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Day', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"second\">".__('Hits', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"last\">".__(ucfirst($visit_type), SLIMSTAT_DOMAIN)."</th>\n";
		$output .= "\t</tr></thead><tbody>\n";
		
		// Today, yesterday, etc...
		$dt_start = $SlimCfg->midnight_db - ($SlimCfg->get['slim_offset'] * 604800);
		$myFilterRange = 604801;
		if ( isset($SlimCfg->get['fd']) ) {
			$myFilterStart = $SlimCfg->get['fd'][0];
			$myFilterEnd = min( $dt_start, $SlimCfg->get['fd'][1] - ($SlimCfg->get['slim_offset'] * 604800) );
			// we don't have to apply $SlimCfg->mktime(), cause we need server time.
			$dt_start = strtotime( date( "Y-m-d 00:00:00", $myFilterEnd ) );
			$myFilterRange = $myFilterEnd - $myFilterStart;
		}

		$dt_limit = $dt_start - min( $myFilterRange, 604800 );
		$current_y = $current_m = 0;
		
		$i = 0; $c = 0;
		$chart_data = array();
		$chart_data[0]['legend'] = __('Hits', SLIMSTAT_DOMAIN);
		$chart_data[1]['legend'] = __(ucfirst($visit_type), SLIMSTAT_DOMAIN);

		for ($dt_midnight = $dt_start; $dt_midnight > $dt_limit; $dt_midnight -= 86400) {
			$hvu = SSFunction::calc_hvu( $dt_midnight, ($dt_midnight + 86399), $type, $filter_clause );
			$label_text = $SlimCfg->my_esc( $SlimCfg->date_str(__('j M, Y', SLIMSTAT_DOMAIN), $dt_midnight, &$current_y, false, false ) );
			$chart_data[0]['text'][$c] = $label_text;
			
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".$label_text." ".$filter_btn."</td>";
			if(max($hvu) > 0) {
				$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_midnight.'|'.($dt_midnight + 86399)), true);
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";

				$chart_data[0]['onclick'][$c] = SSFunction::chart_onclick_url(array('fd'=>$dt_midnight.'|'.($dt_midnight + 86399)));
				$chart_data[0]['values'][$c] = (int)$hvu['hits'];
				$chart_data[1]['values'][$c] = (int)$hvu[$visit_type];
				$i++;
			} else {
				$output .= "<td class=\"second\">0</td>";
				$output .= "<td class=\"last\">0</td>\n";
				$chart_data[0]['onclick'][$c] = false;
				$chart_data[0]['values'][$c] = $chart_data[1]['values'][$c] = 0;
			}
			$output .= "\t</tr>\n";
			$c++;
		}
		$output .= '</tbody></table>';

		$chart = array('type'=>'area', 'tip'=>'#val#<br>#key#');
/*		foreach($chart_data as $k => $data) {
			foreach (array_keys($data) as $dk) {
				if (in_array($dk, array('onclick', 'values', 'text')))
					$chart_data[$k][$dk] = array_reverse($chart_data[$k][$dk]);
			}
		}*/
		$chart['data'] = $chart_data;

		if ($i == 0) {
			$output = 0;
			$chart = false;
		}

		return SSFunction::get_module_custom(__FUNCTION__, $output, $args, $chart);
	}
	
	function _moduleWeeklyHits($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause(false);
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>'force'));

		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<thead><tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Week', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"second\">".__('Hits', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"last\">".__(ucfirst($visit_type), SLIMSTAT_DOMAIN)."</th>\n";
		$output .= "\t</tr></thead><tbody>\n";
		
		if ( isset($SlimCfg->get['fd']) ) {
			$myFilterRange = $SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0];
			$dt_start = $SlimCfg->time_switch($SlimCfg->get['fd'][1], 'blog');
		} else {
			$myFilterRange = 4838401;
			$dt_start = $SlimCfg->time(); // blog now time
		}

		$dt_args = array('h'=>0, 'i'=>0, 's'=>0);
		$dt_args['d'] = 1 - $SlimCfg->date("w", $dt_start, false);
		if ($dt_args['d'] == 0)
			$dt_args['d'] = null;
		elseif ($dt_args['d'] == 1)
			$dt_args['d'] = '+1';
		else
			$dt_args['d'] = strval($dt_args['d']);
		$dt_start = $SlimCfg->mktime($dt_args, $dt_start, 'db');

		$dt_start -= $SlimCfg->get['slim_offset'] * 4838400;
		$dt_limit = $dt_start - min( $myFilterRange, 4838400 );
		
		$i = 0;
		$c = 0;
		$chart_data = array();
		$chart_data[0]['legend'] = __('Hits', SLIMSTAT_DOMAIN);
		$chart_data[1]['legend'] = __(ucfirst($visit_type), SLIMSTAT_DOMAIN);

		for ($dt_monday = $dt_start; $dt_monday > $dt_limit; $dt_monday -= 604800) {
			$week = $SlimCfg->date(__('j M ,Y', SLIMSTAT_DOMAIN), $SlimCfg->time_switch($dt_monday, 'blog'));
			$chart_data[0]['text'][$c] = $week;
			$week .= ' - ' . $SlimCfg->date(__('j M ,Y', SLIMSTAT_DOMAIN), $SlimCfg->time_switch(($dt_monday + 604799), 'blog'));
			$hvu = SSFunction::calc_hvu( $dt_monday, ($dt_monday + 604799), $type, $filter_clause );
			if(max($hvu) > 0) {
				$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_monday.'|'.($dt_monday + 604799)), true);
				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";
				$output .= "\t\t<td class=\"first\">".$week." ".$filter_btn."</td>";
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
				$output .= "\t</tr>\n";

				$chart_data[0]['onclick'][$c] = SSFunction::chart_onclick_url(array('fd'=>$dt_monday.'|'.($dt_monday + 604799)));
				$chart_data[0]['values'][$c] = (int)$hvu['hits'];
				$chart_data[1]['values'][$c] = (int)$hvu[$visit_type];
				$i++;
			} else {
				$chart_data[0]['onclick'][$c] = false;
				$chart_data[0]['values'][$c] = $chart_data[1]['values'][$c] = 0;
			}
			$c++;
		}
		$output .= '</tbody></table>';

		$chart = array('type'=>'area', 'tip'=>'#val#<br>#key#');
		$chart['data'] = $chart_data;

		if ($i == 0) {
			$output = 0;
			$chart = false;
		}

		return SSFunction::get_module_custom(__FUNCTION__, $output, $args, $chart);
	}
	
	function _moduleMonthlyHits($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause(false);
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>'force'));

		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<thead><tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Month', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"second\">".__('Hits', SLIMSTAT_DOMAIN)."</th>";
		$output .= "<th class=\"last\">".__(ucfirst($visit_type), SLIMSTAT_DOMAIN)."</th>\n";
		$output .= "\t</tr></thead><tbody>\n";
		
		$dt = $SlimCfg->time(); // blog now time
		if ( isset($SlimCfg->get['fd']) )
			$dt = $SlimCfg->get['fd'][1];
		// start of this month
		$dt_start = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'd'=>1, 'y'=>'-'.$SlimCfg->get['slim_offset']), $dt, 'db');
		// end of today
		$dt_end = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'd'=>'+1', 'y'=>'-'.$SlimCfg->get['slim_offset']), $dt, 'db') - 1;
		$current_y = 0;

		$j = 0; $c = 0;
		$chart_data = array();
		$chart_data[0]['legend'] = __('Hits', SLIMSTAT_DOMAIN);
		$chart_data[1]['legend'] = __(ucfirst($visit_type), SLIMSTAT_DOMAIN);

		for ($i = 1; $i < 13; $i++) {
			if (isset($SlimCfg->get['fd']) && $dt_end < $SlimCfg->get['fd'][0])
				break;
			$hvu = SSFunction::calc_hvu( $dt_start, $dt_end, $type, $filter_clause );
			$label_text = $SlimCfg->date_str( __("M, Y", SLIMSTAT_DOMAIN), $dt_start, &$current_y );
			$chart_data[0]['text'][$c] = $label_text;
			if(max($hvu) > 0) {
				$filter_btn = SSFunction::get_filterBtns(array('fd'=>$dt_start.'|'.$dt_end), true);
				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";
				$output .= "\t\t<td class=\"first\">".$label_text." ".$filter_btn."</td>";
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"last\">".$hvu[$visit_type]."</td>\n";
				$output .= "\t</tr>\n";

				$chart_data[0]['onclick'][$c] = SSFunction::chart_onclick_url(array('fd'=>$dt_start.'|'.$dt_end));
				$chart_data[0]['values'][$c] = (int)$hvu['hits'];
				$chart_data[1]['values'][$c] = (int)$hvu[$visit_type];
				$j++;
			} else {
				$chart_data[0]['onclick'][$c] = false;
				$chart_data[0]['values'][$c] = $chart_data[1]['values'][$c] = 0;
			}
			$c++;

			$dt_end = $dt_start - 1;
			$dt_start = $SlimCfg->mktime( array('h'=>0, 'i'=>0, 's'=>0, 'm'=>'-'.$i, 'd'=>1, 'y'=>'-'.$SlimCfg->get['slim_offset']), $dt, 'db' );
		}
		$output .= '</tbody></table>';

		$chart = array('type'=>'area', 'tip'=>'#val#<br>#key#');
		$chart['data'] = $chart_data;

		if ($j == 0) {
			$output = 0;
			$chart = false;
		}

		return SSFunction::get_module_custom(__FUNCTION__, $output, $args, $chart);
	}
	
	function _moduleTopResources($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.resource, MAX(ts.dt) maxdt, COUNT(*) countall 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('resource')."
			WHERE ts.resource NOT IN (0,1) 
			AND $filter_clause
			GROUP BY ts.resource 
			ORDER BY countall DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Resource', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=2&amp;ft=0'));
		$cols[1]['html'] = '%1$s '.$filter_btn;
		$cols[1]['formats'][0] = array('resource.resource2title', 'resource.encode');

		$cols[2]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'second';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countall.integer');

		$cols[3]['title'] = __('When', SLIMSTAT_DOMAIN);
		$cols[3]['class'] = 'last';
		$cols[3]['html'] = '%s';
		$cols[3]['formats'][0] = array('maxdt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}
	
	function _moduleTopSearchStrings($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.searchterms, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('searchterms')."
			WHERE ts.searchterms <> ''
				AND $filter_clause 
			GROUP BY ts.searchterms 
			ORDER BY countall DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0]['title'] = __('Search string', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=1&amp;ft=0'));
		$cols[0]['html'] = '%1$s '.$filter_btn;
		$cols[0]['formats'][0] = array('searchterms.medium', 'searchterms.encode');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('Visits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countdist.integer');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleTopLanguages($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT CONCAT('l-', ts.language) language, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('language')."
			WHERE ts.language <> '' 
				AND ts.language <> 'xx'
				AND $filter_clause 
			GROUP BY ts.language 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0]['title'] = __('Language', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=7&amp;ft=0'));
		$cols[0]['html'] = '%1$s '.$filter_btn;
		$cols[0]['formats'][0] = array('language.long_locale', 'language.long');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('Visits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countdist.integer');

		$chart = array('type'=>'pie', 'legend'=>'top', 'tip'=>'#key#<br>#val#%');
		$chart_onclick = SSFunction::chart_onclick_url(array('fi'=>'%s&ff=7&ft=0'));
		$chart['data'][0] = array('text'=>'language.long_locale', 'value'=>'countall.percentage', 'onclick'=>$chart_onclick, 'onclick-formats'=>array('language.long'));

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args, $chart);
	}

	function _moduleTopDomains($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.domain, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('domain')."
			WHERE ts.domain <> '' 
				AND ts.domain NOT IN (".SSFunction::my_domains('db').")
				AND $filter_clause 
			GROUP BY ts.domain 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0]['title'] = __('Domain', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$cols[0]['html'] = '<a title="'.__('Visit this domain', SLIMSTAT_DOMAIN).'"%1$s>%2$s</a>';
		$cols[0]['formats'][0] = array('domain.encode_prefix', 'domain.'.($args['style']=='wide'?'long':'short'));

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('Visits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countdist.integer');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleInternallyReferred($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.resource, ts.referer, COUNT(*) countall
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('domain')."
			WHERE ts.resource NOT IN (0,1)
				AND ts.domain IN (".SSFunction::my_domains('db').")
				AND $filter_clause
			GROUP BY ts.resource
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Resource', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$cols[1]['html'] = '<a title="'.__('Referred by', SLIMSTAT_DOMAIN).' %1$s"%2$s>%3$s</a>';
		$cols[1]['formats'][0] = array('referer.long', 'referer.encode_prefix', 'resource.resource2title');

		$cols[2]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countall.integer');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleTopInternalSearchStrings($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.searchterms, ts.referer, COUNT(*) countall
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('resource')."
			WHERE ts.resource = 1
				AND ts.searchterms <> ''
				AND $filter_clause
			GROUP BY ts.searchterms
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];


		$cols[0]['title'] = __('Search string', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$cols[0]['html'] = '<a title="'.__('Searched in', SLIMSTAT_DOMAIN).' %1$s"%2$s>%3$s</a>';
		$cols[0]['formats'][0] = array('referer.long', 'referer.encode_prefix', 'searchterms.medium');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'last';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleTopRemoteAddresses($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT INET_NTOA(ts.remote_ip) remote_ip_a, COUNT(*) countall, CONCAT( 'c-', LOWER(ts.country)) country 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('remote_ip')."
			WHERE ts.remote_ip <> 0
				AND $filter_clause 
			GROUP BY ts.remote_ip 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];


		$cols[0]['title'] = __('Remote address', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%3$s&amp;ff=3&amp;ft=0'));
		$cols[0]['html'] = '%1$s %2$s '.$filter_btn;
		$cols[0]['formats'][0] = array('country.flag', 'remote_ip_a.remote_ip', 'remote_ip_a.encode');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('%', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countall.percentage');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleTopBrowsers($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.browser, ts.version, COUNT(*) countall 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('browser')."
			WHERE ts.browser <> -1
				AND ts.version <> '' 
				AND $filter_clause 
			GROUP BY ts.browser, ts.version 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];


		$cols[0]['title'] = __('Browser', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$cols[0]['html'] = '%1$s %2$s';
		$cols[0]['formats'][0] = array('browser.b_id2string', 'version.medium');

		$cols[1]['title'] = __('%', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'last';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.percentage');

		$chart = array('type'=>'pie', 'legend'=>'top', 'tip'=>'#key#<br>#val#%');
		$chart['data'][0] = array('text-formats'=>array('browser.b_id2string', 'version.medium'), 'text'=>'%1$s %2$s', 'value'=>'countall.percentage');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args, $chart);
	}

	function _moduleTopPlatforms($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.platform, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('platform')."
			WHERE ts.platform <> -1 
				AND $filter_clause 
			GROUP BY ts.platform 
			ORDER BY countall DESC 
			LIMIT {$SlimCfg->db_offset},".$SlimCfg->option['limitrows']."";

		$cols[0]['title'] = __('Platform', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=5&amp;ft=0'));
		$cols[0]['html'] = '%1$s '.$filter_btn;
		$cols[0]['formats'][0] = array('platform.p_id2string', 'platform.long');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('Visits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countdist.integer');

		$chart = array('type'=>'pie', 'legend'=>'top', 'tip'=>'#key#<br>#val#%');
		$chart_onclick = SSFunction::chart_onclick_url(array('fi'=>'%s&ff=5&ft=0'));
		$chart['data'][0] = array('text'=>'platform.p_id2string', 'value'=>'countall.percentage', 'onclick'=>$chart_onclick, 'onclick-formats'=>array('platform.long'));

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args, $chart);
	}

	function _moduleTopCountries($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT LOWER(CONCAT( 'c-', ts.country)) country, COUNT(*) countall
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('country')."
			WHERE ts.country <> ''
				AND $filter_clause 
			GROUP BY ts.country 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];


		$cols[0]['title'] = __('Country', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%3$s&amp;ff=6&amp;ft=0'));
		$cols[0]['html'] = '%1$s - %2$s '.$filter_btn;
		$cols[0]['formats'][0] = array('country.flag', 'country.long_locale', 'country.long');

		$cols[1]['title'] = __('%', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'last';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.percentage');

		$chart = array('type'=>'pie', 'legend'=>'top', 'tip'=>'#key#<br>#val#%');
		$chart_onclick = SSFunction::chart_onclick_url(array('fi'=>'%s&ff=6&ft=0'));
		$chart['data'][0] = array('text'=>'country.long_locale', 'value'=>'countall.percentage', 'onclick'=>$chart_onclick, 'onclick-formats'=>array('country.long'));

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args, $chart);
	}

	function _moduleTopReferers($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.referer, ts.resource, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('referer')."
			WHERE ts.referer <> '' 
				AND ts.resource NOT IN (0,1)
				AND ts.domain <> '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."' 
				AND $filter_clause 
			GROUP BY ts.referer 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0] = SLIMSTAT_RESOURCE_COL;

		$cols[1]['title'] = __('Referer', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'first';
		$cols[1]['html'] = '<a title="'.__('Visit this referer', SLIMSTAT_DOMAIN).'"%1$s>%2$s</a>';
		$cols[1]['formats'][0] = array('referer.encode_prefix', 'referer.medium');

		$cols[2]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'second';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('countall.integer');

		$cols[3]['title'] = __('Visits', SLIMSTAT_DOMAIN);
		$cols[3]['class'] = 'last';
		$cols[3]['html'] = '%s';
		$cols[3]['formats'][0] = array('countdist.integer');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

	function _moduleTopBrowsersOnly($args='') {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

		$sql[0] = "SELECT ts.browser, COUNT(*) countall 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('browser')."
			WHERE ts.browser <> -1 
				AND $filter_clause 
			GROUP BY ts.browser 
			ORDER BY countall DESC
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0]['title'] = __('Browser', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%2$s&amp;ff=4&amp;ft=0'));
		$cols[0]['html'] = '%1$s '.$filter_btn;
		$cols[0]['formats'][0] = array('browser.b_id2string', 'browser.long');

		$cols[1]['title'] = __('%', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'last';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.percentage');

		$chart = array('type'=>'pie', 'legend'=>'top', 'tip'=>'#key#<br>#val#%');
		$chart_onclick = SSFunction::chart_onclick_url(array('fi'=>'%s&ff=4&ft=0'));
		$chart['data'][0] = array('text'=>'browser.b_id2string', 'value'=>'countall.percentage', 'onclick'=>$chart_onclick, 'onclick-formats'=>array('browser.long'));

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args, $chart);
	}

	function _moduleRecentRemoteip( $filter_clause='' ) {
		global $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>true, 'navi'=>true));

//		$dt_this_hour = strtotime( date( "Y-m-d H:59:59" ) );
		$sql[0] = "SELECT INET_NTOA(ts.remote_ip) remote_ip_a, 
			CONCAT('c-', LOWER(ts.country)) country, MAX(ts.dt) AS maxdt, COUNT(*) countall 
			FROM $SlimCfg->current_table ts
			".$SlimCfg->use_indexkey('remote_ip')."
			WHERE ts.remote_ip <> 0
				AND $filter_clause
			GROUP BY ts.remote_ip 
			ORDER BY maxdt DESC 
			LIMIT {$SlimCfg->db_offset}, ".$SlimCfg->option['limitrows'];

		$cols[0]['title'] = __('Remote IP', SLIMSTAT_DOMAIN);
		$cols[0]['class'] = 'first';
		$filter_btn = SSFunction::get_filterBtns(array('fi'=>'%3$s&amp;ff=3&amp;ft=0', 'moid'=>__FUNCTION__));
		$cols[0]['html'] = '%1$s %2$s '.$filter_btn;
		$cols[0]['formats'][0] = array('country.flag', 'remote_ip_a.remote_ip', 'remote_ip_a.long');

		$cols[1]['title'] = __('Hits', SLIMSTAT_DOMAIN);
		$cols[1]['class'] = 'second';
		$cols[1]['html'] = '%s';
		$cols[1]['formats'][0] = array('countall.integer');

		$cols[2]['title'] = __('Last', SLIMSTAT_DOMAIN);
		$cols[2]['class'] = 'last';
		$cols[2]['html'] = '%s';
		$cols[2]['formats'][0] = array('maxdt.date');

		return SSFunction::getModule(__FUNCTION__, $sql, $cols, $args);
	}

}//end of class

?>