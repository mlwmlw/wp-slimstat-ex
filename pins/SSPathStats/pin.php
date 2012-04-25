<?php
/*
Module Name : PathStats
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Stephen Wettone, Cheon, Young-Min
Author URI : http://wettone.com/, http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
Powered by SlimStat(http://wettone.com/code/slimstat)'s PathStats plugin

Originally written by Stephen Wettone(http://wettone.com/code/slimstat)
*/

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSPathStats extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'PathStats',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Show paths taken by recent visitors',
		'version' => '0.8',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_modulePathStats', 'title' => 'Paths taken by recent visitors' ),
	);

	var $since;
	var $show_crawlers = false; //show crawlers or not?
	var $rows = 50;//limit rows.

	function SSPathStats() {
	}

	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '2.1') {
			return array	('compatible' => false, 'message' => 'PathStat is only compatible with SlimStat-Ex 2.1 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _modulePathStats($args='') {
		global $wpdb, $SlimCfg;
		$filter_clause = SSFunction::get_filter_clause();
		$args = wp_parse_args($args, array('links'=>false, 'navi'=>true));

		$use_ajax = $SlimCfg->option['use_ajax'];
		$offset = $SlimCfg->get['slim_offset'];
		// get max visit
		$query = "SELECT MAX(ts.visit) FROM $SlimCfg->table_stats ts";
		$max_visit = $wpdb->get_var($query);
		
		$str = "";
		
		// get requests
		$query = "SELECT * FROM $SlimCfg->table_stats ts WHERE ";
		if(!$this->show_crawlers) {
			$query .= "ts.browser NOT IN (".implode(',', SSFunction::get_bot_array(true)).") AND ";
		}
		$query .= "ts.visit >= ".( $max_visit - ($this->rows * ($offset + 1) ) );
		$query .= " AND ts.visit < ".( $max_visit - ($this->rows * $offset) );
		$query .= " AND ts.resource NOT IN (0,1) ";
		$query .= " AND {$filter_clause} ";
		$query .= " ORDER BY ts.visit DESC, ts.dt DESC";
		$result = mysql_query( $query );

		$pinid =& $this->getPinID();
		$moid =& $this->getMoID(0);

		if ( $result && 0 < mysql_num_rows($result) ) {
			$prev_visit = 0;
			$visits = array();
			$visit = array();
			$pages = array();
			while ( $assoc = mysql_fetch_assoc( $result ) ) {
				if ( $assoc["visit"] != $prev_visit && !empty( $visit ) ) {
					$visits[] = $visit;
					$visit = array();
				}
				$visit[] = $assoc;
				$prev_visit = $assoc['visit'];
			}
			if ( !empty( $visit ) ) {
				$visits[] = $visit;
			}
			$str .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<thead><tr><th class=\"first\">".__('Visitor', SLIMSTAT_DOMAIN)
				.($use_ajax ? " &ndash; <a href=\"#\" onclick=\"SlimStat.toggleAllSubs(this);return false;\">expand</a> (all)" : "")
				."</th>";
			$str .= "<th class=\"second\">".__('When', SLIMSTAT_DOMAIN)."</th>";
			$str .= "<th class=\"third\">".__('Browser', SLIMSTAT_DOMAIN)."</th>";
			$str .= "<th class=\"third\">".__('Platform', SLIMSTAT_DOMAIN)."</th>";
			$str .= "<th class=\"third last\">".__('Country', SLIMSTAT_DOMAIN)."</th></tr></thead><tbody>\n";
			
			$svr_today = $SlimCfg->midnight_db;
			$i = 0;
			for ($j = 0; $j < count($visits); $j++) {
				$visit = $visits[$j];
				$k = 0;
				while ( isset($visits[$j+$k+1]) && $visits[$j+$k][0]["remote_ip"] == $visits[$j+$k+1][0]["remote_ip"] ) {
					$visit = array_merge($visit, $visits[$j+$k+1]);
					$k++;
				}
//			foreach ( $visits as $visit ) {
				$subcontgl_class = ($i==0 || !$use_ajax) ? '' : ' class="collapsed"';
				$visit_ip = long2ip($visit[0]["remote_ip"]);
				$is_today = ( $visit[0]["dt"] >= $svr_today );
				$mindt = $this->time_label( $visit[0]["dt"] );
				$maxdt = $this->time_label( $visit[ sizeof( $visit )-1 ]["dt"] );
				$filter = array("fi"=> urlencode($visit_ip)."&amp;ff=3&amp;ft=0");

				$str .= "<tr".($use_ajax ? " onclick=\"SlimStat.toggleSub(this);\" style=\"cursor:pointer;\"{$subcontgl_class}" : "").">";
				$str .= "<td class=\"accent first\">".SSFunction::_whoisLink($visit_ip)." (".count($visit).") ".SSFunction::filterBtn($filter, 3).SSFunction::filterBtn($filter, $pinid).($use_ajax ? " <span class=\"subcontgl\">".SSFunction::blank_image('toggle', 'icons')."</span>" : "")."</td>";
				$str .= "<td class=\"accent second\">";
				if ( $is_today ) {
					$str .= ( ( $mindt == $maxdt ) ? $mindt : $mindt."-".$maxdt );
				} else {
					$str .= $this->time_label( $visit[0]["dt"], $SlimCfg->time() );
				}
				$str .= "</td>";
				$str .= "<td class=\"accent third\">".__(SSFunction::_translateBrowserID( $visit[0]["browser"] ), SLIMSTAT_DOMAIN)."";
				if ( $visit[0]["version"] != '' ) {
					$str .= " ".htmlentities( $visit[0]["version"] );
				}
				$str .="</td><td class=\"accent third\">".__(SSFunction::_translatePlatformID($visit[0]["platform"]), SLIMSTAT_DOMAIN)."</td>";
				$country = 'c-'.strtolower($visit[0]["country"]);
				$str .= "<td class=\"accent third last\">".SSFunction::get_flag( $country )." ".__($country, SLIMSTAT_DOMAIN)."</td></tr>\n";
				
				$prev_dt = "";
				$str .= '<tr class="'.($i==0 || !$use_ajax ? 'subcons' : 'collapsed-subcons').'"><td colspan="5"><table border="0" cellspacing="0" cellpadding="0"><tbody>';
				foreach ( $visit as $hit ) {
					SSFunction::setup_row_data(&$hit);
					if(!$use_ajax) $subcon_class = '';
					$resource2title = SSFunction::_guessPostTitle($hit["resource"]);
					$str .= "<tr><td class=\"first\">";
					$str .= "<a href=\"".attribute_escape($hit["resource_url"])."\" class=\"external\"";
					$str .= " title=\"Resource: ".strip_tags($resource2title)."\">";
					$str .= "<img src=\"".$SlimCfg->pluginURL."/css/external.gif\" width=\"9\" height=\"9\" alt=\"go\" /></a>&nbsp;&nbsp;";
					$filter = "&amp;fi=".urlencode($hit["resource"])."&amp;ff=2&amp;ft=0";
					$str .= $resource2title;
					$str .= "</td>";
					$dt_label = $this->time_label( $hit["dt"] );
					if ( ( !$is_today && $prev_dt == "" ) || ( $mindt != $maxdt && $dt_label != $prev_dt ) ) {
						$str .= "<td class=\"second\">".$dt_label."</td>";
					} else {
						$str .= "<td class=\"second\">&nbsp;</td>";
					}
					$prev_dt = $dt_label;
					if ( $hit["referer"] != "" && $hit["domain"] != $_SERVER['HTTP_HOST'] ) {
						$str .= "<td colspan=\"3\" class=\"last third\" style=\"text-align:center;\">";
						$filter = array("fi"=> urlencode( $hit["domain"] )."&amp;ff=0&amp;ft=1");
						$str .= "".htmlentities( $SlimCfg->truncate( $hit["domain"], 30 ) )."&nbsp;&nbsp;";
						$str .= "<a href=\"http://".attribute_escape($hit["referer"])."\" class=\"external\" rel=\"nofollow\"";
						$str .= " title=\"Visit this referer\">";
						$str .= "<img src=\"".$SlimCfg->pluginURL."/css/external.gif\" width=\"9\" height=\"9\" alt=\"\" /></a>";
						$str .= ' '.SSFunction::filterBtn($filter, 3);
					} else {
						$str .= "<td colspan=\"3\" class=\"third\">&nbsp;</td>";
					}
					$str .= "</tr>\n";					
				}
				$str .= '</tbody></table></td></tr>';
				$i++;
				$j += $k;
			}
			
			$str .= "</tbody></table>\n";
		} else {
			$str = '';
		}
		return SSFunction::get_module_custom($moid, $str, $args);
	}

	function time_label( $_dt, $_compared_to_dt=0 ) {
		global $SlimCfg;
		$usr_dt = $SlimCfg->time_switch($_dt, 'blog');
		if ( $_compared_to_dt == 0 ) {
			if ( $SlimCfg->date( "a", $usr_dt ) == "" ) {
				return $SlimCfg->date( "H:i", $usr_dt );
			} else {
				return strtolower( $SlimCfg->date( "g:i a", $usr_dt ) );
			}
			//return strftime( "%r", $usr_dt );
		} elseif ( $_dt >=  $SlimCfg->mktime( array('h'=>0, 'i'=>0, 's'=>0), $_compared_to_dt ) ) {
			return $this->time_label( $_dt );
		} else {
			return $SlimCfg->date( "j M", $usr_dt );
		}
	}

	function _displayPanel() {
		global $SlimCfg;
		echo $this->_filterIntervalLink();
		echo $this->current_filters();
		echo $this->_modulePathStats( array('class'=>'full') );
	}

}//end of class
?>