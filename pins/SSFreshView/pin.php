<?php
/******************************************************************************
 Pepper
 
 Developer      : Kyle Rove
 Plug-in Name   : Fresh View
 Version        : v111
 
 kyle.rove@gmail.com
 http://www.sensoryoutput.com/projects/freshview/
 
 Please email kyle.rove@gmail.com with comments, bugs, or feature requests.
 THIS SOFTWARE IS PROVIDED AS IS.

 PayPal donations to my email address are most appreciated. (Support a poor
 medical student :-)

 This work is licensed under the Creative Commons Attribution-ShareAlike
 License. To view a copy of this license, visit
 http://creativecommons.org/licenses/by-sa/2.5/ or send a letter to
 
   Creative Commons
   543 Howard Street, 5th Floor
   San Francisco, California, 94105
   USA

Wp-SlimStat-Ex Pin port by 082net(http://082net.com/)
 ******************************************************************************/
if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSFreshView extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'FreshView',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'This Pin ported from <a href="http://www.sensoryoutput.com/projects/freshview/">FreshView Pepper</a>. Display your blog stats in a visually-stunning, minty fresh SVG format. Please note that your browser must be SVG-compatible.',
		'version' => '0.3',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => 'getHTML_PastDay', 'title' => 'Past Day' ),
		1 => array( 'name' => 'getHTML_PastWeek', 'title' => 'Past Week' ),
		2 => array( 'name' => 'getHTML_PastMonth', 'title' => 'Past Month' ),
		3 => array( 'name' => 'getHTML_PastYear', 'title' => 'Past Year' ),
	);

	var $prefs = array(
		'24HourTime' => 0, // show 24 hour time instead of 12 hour time
		'cachelimit' => 60 // svg(image) cache time by '''minutes'''
	);

	var $graphParams = array( 'svgW' => 555,
							  'svgH' => 312,
							  'graphAreaX' => 48,
							  'graphAreaY' => 9,
							  'graphAreaW' => 480,
							  'graphAreaH' => 256,
							  'graphDivYMin' => 2,
							  'graphDivYMax' => 9,
							  'xValueInt' => 7,
							  'yValueInt' => 1 );
	
	var $powered_by = '<span class="filter_string">Powered by <a href="http://www.sensoryoutput.com/projects/freshview/">FreshView Pepper</a></span>';

	function SSFreshView() {
		global $SlimCfg;
		$this->path = dirname(__FILE__).'/';
		$this->pinURL = $SlimCfg->pluginURL . '/pins/SSFreshView/';
	}

	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '1.5') {
			return array	('compatible' => false, 'message' => 'FreshView is only compatible with SlimStat-Ex 1.5 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function update_graphParams($newParams) {
		$this->graphParams = array_merge($this->graphParams, $newParams);
	}

	function _displayPanel() {
		global $SlimCfg;
		// trick filter encode query
		$_tb = !empty($SlimCfg->get['slim_table']) ? $SlimCfg->get['slim_table'] : 'all';
		$html = '';
		$html .= $this->switchTable();

		$html .= $this->getHTML_PastDay($_tb);
		$html .= $this->getHTML_PastWeek($_tb);
		$html .= $this->getHTML_PastMonth($_tb);
		$html .= $this->getHTML_PastYear($_tb);

		echo $html;
	}

	function switchTable() {
		global $SlimCfg;
		$output = "";
		$filter_img = "<img src=\"".$SlimCfg->pluginURL."/css/filter-self.gif\" alt=\"Filter\" style=\"vertical-align:bottom;\" />";
		$pinid =& $this->getPinID();
//		$output .= "<br />\n";
		$output .= "\t<p class=\"interval-filter\">&nbsp;&nbsp;<span>".__('Select Table', 'wp-slimstat-ex')." : \n";
		// All
		$href = SSFunction::get_url(array('slim_table'=>''));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View chart for &#039;All stats&#039;', 'wp-slimstat-ex')."\">";
		$output .= __('All', 'wp-slimstat-ex').$filter_img."</a> | ";
		// Common
		$href = SSFunction::get_url(array('slim_table'=>'common'));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View chart for &#039;common stats&#039;', 'wp-slimstat-ex')."\">";
		$output .= __('Common', 'wp-slimstat-ex').$filter_img."</a> | ";
		// Feed
		$href = SSFunction::get_url(array('slim_table'=>'feed'));
		$output .= "<a class=\"ajax-request-link\" href=\"".$href."\" title=\"".__('View chart for &#039;feed stats&#039;', 'wp-slimstat-ex')."\">";
		$output .= __('Feed', 'wp-slimstat-ex').$filter_img."</a> | ".$this->powered_by;
		$output .= "</p>\n";
		return $output;
	}

	function object_tag($file) {
		$html = '
  <object type="image/svg+xml" name="pastDayGraph" width="100%" height="' . $this->graphParams['svgH'] . '" data="' . $this->pinURL .'graph_freshview.php'. $file . '" style="border-top: 1px solid #e3f1cb; border-bottom: 1px solid #e3f1cb; margin: 0; padding: 0; background-color: #edf7df;" codebase="http://www.adobe.com/svg/viewer/install/">
	<param name="src" value="' . $this->pinURL .'graph_freshview.php'. $file . '" />
	<span style="margin:10px;padding:10px;text-align:center;overflow:hidden;display:block;">Your browser does not support Scalable Vector Graphics. Install the <a href="http://www.adobe.com/svg/viewer/install/auto/" title="Adobe SVG Viewer Download Area">Adobe SVG Viewer</a>, or upgrade to an SVG-compatible web browser, like <a href="http://getfirefox.com/">Firefox</a>.</span>
	<span style="margin:10px;padding:10px;text-align:center;overflow:hidden;display:block;">Visit the <a href="http://www.sensoryoutput.com/freshview/">Fresh View home page</a> for more information about viewing these SVG graphs.</span>
  </object>
';
	return $html;
	}


	/**************************************************************************
	 getHTML_PastDay()
	 **************************************************************************/
	function getHTML_PastDay($_tb='') {
		if(empty($_tb) || $_tb == SLIMSTAT_DEFAULT_FILTER) $_tb = empty($SlimCfg->get['slim_table']) ? 'all' : $SlimCfg->get['slim_table'];
/*		$file = 'caches/pastday_graph_'.$_tb.'.svg';
		if (!file_exists($this->path . $file) || ((time() - $this->prefs['dayCacheTime']) > filemtime($this->path . $file))) {
			$this->generateSVG_PastDay($_tb);
		}*/
		$file = '?ssfv_interval=day&amp;ssfv_type='.$_tb;
		$html = $this->object_tag($file);
		$moid =& $this->getMoID(0);
		return SSFunction::get_module_custom($moid, $html, array('class'=>'wide', 'style'=>array('height'=>'342px')));
		
	}
	
	/**************************************************************************
	 getHTML_PastWeek()
	 **************************************************************************/
	function getHTML_PastWeek($_tb='') {
		if(empty($_tb) || $_tb == SLIMSTAT_DEFAULT_FILTER) $_tb = empty($SlimCfg->get['slim_table']) ? 'all' : $SlimCfg->get['slim_table'];
/*		$file = 'caches/pastweek_graph_'.$_tb.'.svg';
		if (!file_exists($this->path . $file) || ((time() - $this->prefs['weekCacheTime']) > filemtime($this->path . $file))) {
			$this->generateSVG_PastWeek($_tb);
		}*/
		$file = '?ssfv_interval=week&amp;ssfv_type='.$_tb;
		$html = $this->object_tag($file);
		$moid =& $this->getMoID(1);
		return SSFunction::get_module_custom($moid, $html, array('class'=>'wide', 'style'=>array('height'=>'342px')));
		}


	/**************************************************************************
	 getHTML_PastMonth()
	 **************************************************************************/
	function getHTML_PastMonth($_tb='') {
		if(empty($_tb) || $_tb == SLIMSTAT_DEFAULT_FILTER) $_tb = empty($SlimCfg->get['slim_table']) ? 'all' : $SlimCfg->get['slim_table'];
/*		$file = 'caches/pastmonth_graph_'.$_tb.'.svg';
		if (!file_exists($this->path . $file) || ((time() - $this->prefs['monthCacheTime']) > filemtime($this->path . $file))) {
			$this->generateSVG_PastMonth($_tb);
		}*/
		$file = '?ssfv_interval=month&amp;ssfv_type='.$_tb;
		$html = $this->object_tag($file);
		$moid =& $this->getMoID(2);
		return SSFunction::get_module_custom($moid, $html, array('class'=>'wide', 'style'=>array('height'=>'342px')));
		}
		
	/**************************************************************************
	 getHTML_PastYear()
	 **************************************************************************/
	function getHTML_PastYear($_tb='') {
		if(empty($_tb) || $_tb == SLIMSTAT_DEFAULT_FILTER) $_tb = empty($SlimCfg->get['slim_table']) ? 'all' : $SlimCfg->get['slim_table'];
/*		$file = 'caches/pastyear_graph_'.$_tb.'.svg';
		if (!file_exists($this->path . $file) || ((time() - $this->prefs['yearCacheTime']) > filemtime($this->path . $file))) {
			$this->generateSVG_PastYear($_tb);
		}*/
		$file = '?ssfv_interval=year&amp;ssfv_type='.$_tb;
		$html = $this->object_tag($file);
		$moid =& $this->getMoID(3);
		return SSFunction::get_module_custom($moid, $html, array('class'=>'wide', 'style'=>array('height'=>'342px')));
		}

	function generateSVG_PastDay($_tb) {
		global $SlimCfg;
//		$dt_this_hour = strtotime( date( "Y-m-d H:59:59" ) );
		$dt_this_hour = $SlimCfg->mktime(array('i'=>59, 's'=>59), $SlimCfg->time());// blog time
		$thisHourFormatted = $SlimCfg->date('H', $dt_this_hour, false);// do not translate
		$dt_this_hour = $SlimCfg->time_switch($dt_this_hour, 'db');// server time
		$hvu = array();
		for ($i = 0; $i < 24; $i++) {
			$j = $dt_this_hour - ($i * 60 * 60);
			$hvu = SSFunction::calc_hvu( ( $j - 3599 ), $j, $_tb );
			$statsData[] = array( 'hour' => $SlimCfg->date('H' , $SlimCfg->time_switch($j, 'blog')),
				  'hits' => $hvu['hits'],
				  'uniques' => $hvu[$SlimCfg->option['visit_type']] );
		}
	  
		// Process the data
		$graphScale = $this->getScale($statsData);
		$scaledData = $this->transformData($statsData, $graphScale, 'hour');
		
		// Layout the data
		$svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'day');
		
		// Show only every other hour
		if ($thisHourFormatted % 2 == 1 ) { $svg['oddhour-visible'] = 'visible'; $svg['evnhour-visible'] = 'hidden'; }
		else { $svg['evnhour-visible'] = 'visible'; $svg['oddhour-visible'] = 'hidden'; }
		
		// Put the data into the template and save
		$svgTemplatePath = $this->path."templates/pastday_template.svg";
		$svgCachePath = $this->path."caches/pastday_graph_".$_tb.".svg";
		$svgFile = $this->svgTemplate($svgTemplatePath, $svg);
		return $svgFile;
//		$this->saveFile($svgCachePath, $svgFile);
	}

    /**************************************************************************
     getHTML_DataError()
     **************************************************************************/
    function generateSVG_DataError() {
        $svg['err_msg'] = 'There was an unknown problem getting requested module.';

        $svgTemplatePath = $this->path."templates/error_template.svg";
        $svgFile = $this->svgTemplate($svgTemplatePath, $svg);
        return $svgFile;   
        }

	function generateSVG_PastWeek($_tb) {
		global $SlimCfg;
		$day = $SlimCfg->midnight_db;
		$todayDay = $SlimCfg->date('D', $SlimCfg->midnight_blog);
		// Past 7 days
		for ($i = 0; $i < 7; $i++) {
			$j = $day - ($i * 60 * 60 * 24);
			$hvu = SSFunction::calc_hvu( $j, ($j + 86399), $_tb);
			$statsData[] = array( 'day' => $SlimCfg->date('D', $SlimCfg->time_switch($j, 'blog')),
				  'hits' => $hvu['hits'],
				  'uniques' => $hvu[$SlimCfg->option['visit_type']] );
		}
	  
		// Process the data
		$graphScale = $this->getScale($statsData);
		$scaledData = $this->transformData($statsData, $graphScale, 'day');
		
		// Layout the data
		$svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'week');
		
		// Weekend Highlighting
		if (in_array($todayDay,array('Mon','Tue','Wed','Thu','Fri'))) {
			$svg['weekend-1-visible'] = 'visible';
			$svg['weekend-text-1-visible'] = 'visible';
			$svg['weekend-1_w'] = 2 * $svg['grid_w'];
			$svg['weekend-2-visible'] = 'hidden';
			$svg['weekend-text-2-visible'] = 'hidden';
			$svg['weekend-2_x'] = 0;
			$svg['weekend-2_w'] = 0;
			}
		switch ($todayDay) {
			case 'Mon':
				$svg['weekend-1_x'] = $svg['graph_region_x'] + (3.5 * $svg['grid_w']);
				break;
			case 'Tue':
				$svg['weekend-1_x'] = $svg['graph_region_x'] + (2.5 * $svg['grid_w']);
				break;
			case 'Wed':
				$svg['weekend-1_x'] = $svg['graph_region_x'] + (1.5 * $svg['grid_w']);
				break;
			case 'Thu':
				$svg['weekend-1_x'] = $svg['graph_region_x'] + (0.5 * $svg['grid_w']);
				break;
			case 'Fri':
				$svg['weekend-text-1-visible'] = 'hidden';
				$svg['weekend-1_x'] = $svg['graph_region_x'];
				$svg['weekend-1_w'] = 1.5 * $svg['grid_w'];
				break;
			case 'Sat':
				$svg['weekend-1-visible'] = 'visible';
				$svg['weekend-text-1-visible'] = 'hidden';
				$svg['weekend-1_x'] = $svg['graph_region_x'];
				$svg['weekend-1_w'] = 0.5 * $svg['grid_w'];
				$svg['weekend-2-visible'] = 'visible';
				$svg['weekend-text-2-visible'] = 'hidden';
				$svg['weekend-2_x'] = $svg['graph_region_x'] + ($svg['grid_w'] * 5.5);
				$svg['weekend-2_w'] = 0.5 * $svg['grid_w'];
				break;
			case 'Sun':
				$svg['weekend-1-visible'] = 'hidden';
				$svg['weekend-text-1-visible'] = 'hidden';
				$svg['weekend-1_x'] = 0;
				$svg['weekend-1_w'] = 0;
				$svg['weekend-2-visible'] = 'visible';
				$svg['weekend-text-2-visible'] = 'hidden';
				$svg['weekend-2_x'] = $svg['graph_region_x'] + ($svg['grid_w'] * 4.5);
				$svg['weekend-2_w'] = 1.5 * $svg['grid_w'];
				break;
			}
  
		// Put the data into the template and save
		$svgTemplatePath = $this->path."templates/pastweek_template.svg";
		$svgCachePath = $this->path."caches/pastweek_graph_".$_tb.".svg";
		$svgFile = $this->svgTemplate($svgTemplatePath, $svg);
		return $svgFile;
//		$this->saveFile($svgCachePath, $svgFile);
	}

	function generateSVG_PastMonth($_tb) {
		global $SlimCfg;
		$day = $SlimCfg->midnight_db;

		for ($i = 0; $i < 29; $i++) {
			$j = $day - ($i * 60 * 60 * 24);
			$hvu = SSFunction::calc_hvu( $j, ($j + 86399), $_tb);
			$statsData[] = array( 'day' => $SlimCfg->date('M j', $SlimCfg->time_switch($j, 'blog')),
				  'hits' => $hvu['hits'],
				  'uniques' => $hvu[$SlimCfg->option['visit_type']] );
		}
		
		// Process the data
		$graphScale = $this->getScale($statsData);
		$scaledData = $this->transformData($statsData, $graphScale, 'day');
		
		// Layout the data
		$svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'month');
		
		// Weekend Highlighting
		$todayDay = $SlimCfg->date('D', $SlimCfg->midnight_print, false);
		for ($i = 1; $i <= 5; $i++) {
			if (($i == 1 || $i == 5) && (in_array($todayDay,array('Mon','Tue','Wed','Thu','Fri')))) {
				$svg['weekend-1-visible'] = 'visible';
				$svg['weekend-1_w'] = 2 * $svg['grid_w'];
				$svg['weekend-5-visible'] = 'hidden';
				$svg['weekend-5_x'] = 0;
				$svg['weekend-5_w'] = 0;
			}
			switch ($todayDay) {
				case 'Mon':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (4.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
				break;
				case 'Tue':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (3.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
				break;
				case 'Wed':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (2.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
				break;
				case 'Thu':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (1.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
				break;
				case 'Fri':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + (0.5 * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
				break;
				case 'Sat':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + ((0 - 0.5) * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
					if ($i == 1 || $i == 5) {
						$svg['weekend-1-visible'] = 'visible';
						$svg['weekend-1_x'] = $svg['graph_region_x'];
						$svg['weekend-1_w'] = 1.5 * $svg['grid_w'];
						$svg['weekend-5-visible'] = 'visible';
						$svg['weekend-5_w'] = 0.5 * $svg['grid_w'];
						}
				break;
				case 'Sun':
					$svg['weekend-'.$i.'_x'] = $svg['graph_region_x'] + ((0 - 1.5) * $svg['grid_w']) + (($i - 1) * $svg['grid_w'] * 7);
					if ($i == 1 || $i == 5) {
						$svg['weekend-1-visible'] = 'visible';
						$svg['weekend-1_x'] = $svg['graph_region_x'];
						$svg['weekend-1_w'] = 0.5 * $svg['grid_w'];
						$svg['weekend-5-visible'] = 'visible';
						$svg['weekend-5_w'] = 1.5 * $svg['grid_w'];
						}
				break;
			}
		}
		  
		// Put the data into the template and save
		$svgTemplatePath = $this->path."templates/pastmonth_template.svg";
		$svgCachePath = $this->path."caches/pastmonth_graph_".$_tb.".svg";
		$svgFile = $this->svgTemplate($svgTemplatePath, $svg);
		return $svgFile;
//		$this->saveFile($svgCachePath, $svgFile);
	}
		
	/**************************************************************************
	 generateSVG_PastYear()
	 **************************************************************************/
	function generateSVG_PastYear($_tb) {
		global $SlimCfg;

		$dt = $SlimCfg->time(); // blog now time
//		$dt_start = mktime( 0, 0, 0, date("m", $dt), 1, date("Y", $dt) ); // start of this month
//		$dt_end = mktime( 0, 0, 0, date( "n", $dt ), date( "d", $dt ) + 1 ); // end of today
//		$dt_start = $SlimCfg->time($dt_start, true);
//		$dt_end = $SlimCfg->time($dt_end, true);
		$dt_start = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'd'=>1), $dt, 'db');
		$dt_end = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'd'=>'+1'), $dt, 'db');
		
		for ($i = 0; $i < 12; $i++) {
			$hvu = SSFunction::calc_hvu( $dt_start, $dt_end, $_tb );
			$statsData[] = array('month' => $SlimCfg->date('M', $SlimCfg->time_switch($dt_start, 'blog')),
				  'hits' => $hvu['hits'],
				  'uniques' => $hvu[$SlimCfg->option['visit_type']] );
			$dt_end = $dt_start - 1;
//			$dt_start = mktime( 0, 0, 0, (date("m", $dt) - $i - 1), 1, date("Y", $dt) );
//			$dt_start = $SlimCfg->time($dt_start, true);
			$dt_start = $SlimCfg->mktime(array('h'=>0, 'i'=>0, 's'=>0, 'm'=>'-'.($i+1)), $dt, 'db');
		}

		// Process the data
		$graphScale = $this->getScale($statsData);
		$scaledData = $this->transformData($statsData, $graphScale, 'month');
		
		// Layout the data
		$svg = $this->generateLayout($statsData, $scaledData, $graphScale, 'year');
  
		// Put the data into the template and save
		$svgTemplatePath = $this->path."templates/pastyear_template.svg";
		$svgCachePath = $this->path."caches/pastyear_graph_".$_tb.".svg";
		$svgFile = $this->svgTemplate($svgTemplatePath, $svg);
		return $svgFile;
//		$this->saveFile($svgCachePath, $svgFile);
		}

	/**************************************************************************
	 getScale()
	 **************************************************************************/
	function getScale($statsData) {
		// Extract hits, uniques into simpler array
		for ($i = 0; $i < count($statsData); $i++) {
			$hits = $statsData[$i]['hits']; $uniques = $statsData[$i]['uniques'];
			$rawArray[] = $hits;			$rawArray[] = $uniques;
			}
	
		$maxValue = max($rawArray);
		$maxDiv = $this->graphParams['graphDivYMax'];
		$minDiv = $this->graphParams['graphDivYMin'];
		$yScale = 0;

		if ($maxValue == 0) {
			$yScale = 50;
			$maxDiv = 4;
			}
	
		while(!$yScale) {
			if ($maxDiv > $minDiv && $maxDiv != 5) {
				$scaleMax = $maxValue + (5 - $maxValue % 5);
				}
			else if ($maxDiv > $minDiv && $maxDiv == 5) {
				$scaleMax = $maxValue + (5 - $maxValue % 5);
				$maxDiv--;
				}
			else {
				$maxValue += 5;
				$scaleMax = $maxValue + (5 - $maxValue % 5);
				$maxDiv = $this->graphParams['graphDivYMax'];
				}
			
			if (($scaleMax % $maxDiv) == 0) { $yScale = $scaleMax; }
			else { $maxDiv--; }
			}
	
		// Create array with data scale max, divisions
		$graphScale = array( 'data_max' => $yScale,
							 'data_div' => ($maxDiv) );
								 
		return $graphScale;
		}

	/**************************************************************************
	 transformData()
	 **************************************************************************/
	function transformData($statsData, $graphScale, $timeUnit) {
		$graphAreaH = $this->graphParams['graphAreaH'];
		$scaleFactor = $graphAreaH / $graphScale['data_max'];

		for ($i = (count($statsData) - 1); $i >= 0; $i--) {
			$time = $statsData[$i][$timeUnit];
			$hits = $statsData[$i]['hits'];
			$uniques = $statsData[$i]['uniques'];
			
			// Y Scale data
			$hits = $hits * $scaleFactor;
			$uniques = $uniques * $scaleFactor;
			
			// Y Mirror data
			$hits = $graphAreaH - $hits;
			$uniques = $graphAreaH - $uniques;
			
			// Y Transform data
			$hits = $hits + $this->graphParams['graphAreaY'];
			$uniques = $uniques + $this->graphParams['graphAreaY'];
			
			// Round off
			$hits = round($hits,0);
			$uniques = round($uniques,0);
			
			$scaledData[] = array($timeUnit => $time,
								  'hits' => $hits,
								  'uniques' => $uniques);
			}
			
		return $scaledData;
		}

	/**************************************************************************
	 generateLayout()
	 **************************************************************************/
	 function generateLayout($statsData,$scaledData,$graphScale,$timeUnit) {
	 
		// How many data sets and data points per set?
		$dataSets = count($scaledData[0]) - 1;
		$dataPoints = count($scaledData);
	 
		// Background
		$svg['svg_w'] = $this->graphParams['svgW'];
		$svg['svg_h'] = $this->graphParams['svgH'];
		$svg['css_path'] = $this->pinURL . 'styles.css';
		$svg['js_path'] = $this->pinURL . 'graph.js';
		
		// Grid pattern
		$svg['grid_w'] = round($this->graphParams['graphAreaW'] / ($dataPoints - 1),2);
		$svg['grid_h'] = $this->graphParams['graphAreaH'] / $graphScale['data_div'];
		
		// Graphing region
		$svg['graph_region_x'] = $this->graphParams['graphAreaX'];
		$svg['graph_region_x2'] = $this->graphParams['graphAreaX'] + $this->graphParams['graphAreaW'];
		$svg['graph_region_y'] = $this->graphParams['graphAreaY'];
		$svg['graph_region_y2'] = $this->graphParams['graphAreaY'] + $this->graphParams['graphAreaH'];
		$svg['graph_region_w'] = $this->graphParams['graphAreaW'];
		$svg['graph_region_h'] = $this->graphParams['graphAreaH'];
		$svg['y-line-template_x'] = 7 + $this->graphParams['graphAreaW'];
		$svg['y-line-start_x'] = $this->graphParams['graphAreaX'] - 7;
		$svg['weekend_w'] = 2 * $svg['grid_w'];
		$svg['weekend_h'] = $svg['graph_region_y2'];
		$svg['weekend-text_x'] = $svg['grid_w'] - 25;
		$svg['x-axis-label_y'] = $svg['graph_region_y2'] + 15;
  
		// X axis
		$xAxis = false;
		$dataMarks = '';
		$dataLabels = '';
		$labelCount = 0;
		for ($j = 0; $j < $dataPoints; $j++) {
	  
			if (!$xAxis) {
				if ($timeUnit == 'year') {
					/* Past Year ***********************************************/
					$keyName = 'x-axis_' . strtolower($scaledData[$j]['month']);
					$svg[$keyName] = round($svg['graph_region_x'] + ($j * $svg['grid_w']),1);
					if ($j == 11) { $xAxis = true; }
					}
				else if ($timeUnit == 'month') {
					/* Past Month ***********************************************/
					if (($j % 7 == 0) || $j == 0 || $j == 28) {
						$labelCount++;
						$svg['x-axis_'.$labelCount] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
						$svg['x-axis-label_'.$labelCount] = $scaledData[$j]['day'];
						}
					if ($j == 28) { $xAxis = true; }
					}
				else if ($timeUnit == 'week') {
					$keyName = 'x-axis_' . strtolower(substr($scaledData[$j]['day'],0,2));
					$svg[$keyName] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
					if ($j == 6) { $xAxis = true; }
					}
				else if ($timeUnit == 'day') {
					$hourFormats = array('01'=>'1a','02'=>'2a','03'=>'3a','04'=>'4a','05'=>'5a','06'=>'6a',
										 '07'=>'7a','08'=>'8a','09'=>'9a','10'=>'10a','11'=>'11a','12'=>'12p',
										 '13'=>'1p','14'=>'2p','15'=>'3p','16'=>'4p','17'=>'5p','18'=>'6p',
										 '19'=>'7p','20'=>'8p','21'=>'9p','22'=>'10p','23'=>'11p','00'=>'12a');
					$keyName = 'x-axis_' . $scaledData[$j]['hour'];
					$keyLabelName = 'x-axis-label_' . $scaledData[$j]['hour'];
					$svg[$keyName] = $svg['graph_region_x'] + ($j * $svg['grid_w']);
					if ($this->prefs['24HourTime'] == 1) { $svg[$keyLabelName] = $scaledData[$j]['hour']; }
					else { $svg[$keyLabelName] = $hourFormats[$scaledData[$j]['hour']]; }
					if ($j == 23) { $xAxis = true; }
					}
				}

			// Define limits for svg tooltip
			$rtLim = $svg['graph_region_x'] + $svg['graph_region_w'];
			$ltLim = $svg['graph_region_x'];
			$topLim = $svg['graph_region_y'];
			$curX = $svg['graph_region_x'] + ($svg['grid_w'] * $j);
			$curYHit = $scaledData[$j]['hits'];
			$curYUnique = $scaledData[$j]['uniques'];
	  
			$hitLabelCoords = $this->getTooltipCoords($rtLim, $ltLim, $topLim, $curX,$curYHit);
			$uniqueLabelCoords = $this->getTooltipCoords($rtLim, $ltLim, $topLim, $curX,$curYUnique); 
	  
			if ($j == 0) {
				// Define line origin
				$hitsLineCoords  = 'M' . $svg['graph_region_x'] . ',' . $scaledData[$j]['hits'];
				$hitsAreaCoords  = 'M' . $svg['graph_region_x'] . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'L' . $svg['graph_region_x'] . ',' . $scaledData[$j]['hits'];
				$uniquesLineCoords  = 'M' . $svg['graph_region_x'] . ',' . $scaledData[$j]['uniques'];
				$uniquesAreaCoords  = 'M' . $svg['graph_region_x'] . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'L' . $svg['graph_region_x'] . ',' . $scaledData[$j]['uniques'];
				}
			else {
				// Define line
				$hitsLineCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['hits'];
				$hitsAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['hits'];
				$uniquesLineCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['uniques'];
				$uniquesAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . ',' . $scaledData[$j]['uniques'];
				}
					
			$dataMarks .= '		<use id="1_' . $j . '" x="' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . '" y="' . $scaledData[$j]['hits'] . '" xlink:href="#vertex"/>' . "\n" . '		<use id="2_' . $j . '" x="' . ($svg['graph_region_x'] + ($svg['grid_w'] * $j)) . '" y="' . $scaledData[$j]['uniques'] . '" xlink:href="#vertex"/>' . "\n";
			$dataLabels .= '		<text id="label_1_' . $j . '" x="' . $hitLabelCoords['x'] . '" y="' . $hitLabelCoords['y'] . '" visibility="hidden">' . number_format($statsData[$dataPoints - $j - 1]['hits']) . ' total</text>' . "\n" . '		<text id="label_2_' . $j . '" x="' . $uniqueLabelCoords['x'] . '" y="' . $uniqueLabelCoords['y'] . '" visibility="hidden">' . number_format($statsData[$dataPoints - $j - 1]['uniques']) . ' uniques</text>' . "\n";
			}
	
		$hitsAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * ($dataPoints - 1))) . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'Z';
		$uniquesAreaCoords .= 'L' . ($svg['graph_region_x'] + ($svg['grid_w'] * ($dataPoints - 1))) . ',' . ($svg['graph_region_y'] + $svg['graph_region_h']) . 'Z';
	
		$svg['data-area_1'] = $hitsAreaCoords;
		$svg['data-line_1'] = $hitsLineCoords;
		$svg['data-area_2'] = $uniquesAreaCoords;
		$svg['data-line_2'] = $uniquesLineCoords;
		$svg['data-marks']  = $dataMarks;
		$svg['data-labels'] = $dataLabels;

		// Y axis values
		$svg['y-axis'] = '';
		for ($i = 0; $i < ($graphScale['data_div'] + 1); $i++) {
			if ($i % $this->graphParams['yValueInt'] == 0 && $i != 0 && $i != $graphScale['data_div']) {
				$svg['y-axis'] .= '	  <text x="' . ($svg['graph_region_x'] - 10) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i)) + 4.5) . '">' . round($graphScale['data_max'] / $graphScale['data_div'] * $i,0) . '</text>' . "\n"
							   .  '	  <use x="' . ($svg['graph_region_x'] - 7) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i))) . '" xlink:href="#y-line"/>' . "\n";
				}
			else {
				$svg['y-axis'] .= '	  <text x="' . ($svg['graph_region_x'] - 10) . '" y="' . (($svg['graph_region_y'] + $svg['grid_h'] * ($graphScale['data_div'] - $i)) + 4.5) . '">' . round($graphScale['data_max'] / $graphScale['data_div'] * $i,0) . '</text>' . "\n";
				}
			}
		return $svg;
		}

	/**************************************************************************
	 getTooltipCoords()
	 **************************************************************************/
	function getTooltipCoords($rtLim, $ltLim, $topLim, $curX, $curY) {
		// Determine if hover will fit in graph region
		if (($curX + 45) > $rtLim) { $labelX = $curX - (45 - ($rtLim - $curX)); } // We are past it on the right
		else if (($curX - 45) < $ltLim) { $labelX = $curX + (45 - ($curX - $ltLim)); } // We are past it on the left
		else { $labelX = $curX; } // We are in the green x-wise
		if (($curY - 30) < $topLim) { $labelY = $curY + 30; } // We are above y-wise
		else { $labelY = $curY - 5; } // We are in the green y-wise
		
		return array('x' => $labelX, 'y' => $labelY);
		}

	/**************************************************************************
	 svgTemplate()
	 
	 Reads the SVG template file and replaces variables
	 **************************************************************************/
	function svgTemplate($svgTemplatePath, $data) {
		$file = fopen($svgTemplatePath,"r");
		$svgFile = fread($file, filesize($svgTemplatePath));
		
		foreach ($data as $key => $value) {
			$svgFile = str_replace("%$key%", $value, $svgFile);	
			}
	  
		// close out the file handler
		fclose($file);
	  
		// return XML
		return $svgFile;
		}
		
	/**************************************************************************
	 saveFile()
	 
	 Takes SVG data and saves it
	 **************************************************************************/
/*	function saveFile($svgCachePath, $svgFile) {
		// Let's make sure the file exists and is writable first.
		if (is_writable($svgCachePath)) {
			if (!$cache = fopen($svgCachePath, 'w')) {
				exit;
			}

			// Write $theContent to our opened file.
			if (fwrite($cache, $svgFile) === FALSE) {
				exit;
				}	
			
			fclose($cache);
			}
		}	*/
}
?>