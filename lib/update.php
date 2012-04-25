<?php
if ( !defined('SLIMSTATPATH') ) {
	die("Sorry, we do not allow direct or external access.");
}

include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

class GeoIP_Upgrader extends WP_Upgrader {

	var $result;
	var $geofile;

	function init() {
		global $SlimCfg;
		parent::init();
		$this->geofile = $SlimCfg->geoip == 'city' ? 'GeoLiteCity.dat' : 'GeoIP.dat';
	}

	function update_strings() {
		$this->strings['up_to_date'] = __('GeoIP data is at the latest version.', SLIMSTAT_DOMAIN);
		$this->strings['no_package'] = __('GeoIP data not available.', SLIMSTAT_DOMAIN);
		$this->strings['downloading_package'] = __('Downloading update from <span class="code">%s</span>.', SLIMSTAT_DOMAIN);
		$this->strings['unpack_package'] = __('Unpacking the update.', SLIMSTAT_DOMAIN);
		$this->strings['remove_old'] = __('Removing the old version of GeoIP data.', SLIMSTAT_DOMAIN);
		$this->strings['remove_old_failed'] = __('Could not remove the old GeoIP data.', SLIMSTAT_DOMAIN);
		$this->strings['process_failed'] = __('GeoIP data upgrade Failed.', SLIMSTAT_DOMAIN);
		$this->strings['process_success'] = __('GeoIP data upgraded successfully.', SLIMSTAT_DOMAIN);
		$this->strings['file_exists'] = __('Destination file already exists.', SLIMSTAT_DOMAIN);
		$this->strings['copy_failed'] = __('Could not copy file.', SLIMSTAT_DOMAIN);
	}

	function unpack_package($package, $delete_package = true) {
		global $wp_filesystem;

		$this->skin->feedback('unpack_package');

		$upgrade_folder = $wp_filesystem->wp_content_dir() . 'upgrade/';
		if ( !$wp_filesystem->is_dir($upgrade_folder) )
			if ( !$wp_filesystem->mkdir($upgrade_folder, FS_CHMOD_DIR) )
				return new WP_Error('mkdir_failed', $this->strings['mkdir_failed'], $upgrade_folder);

		//Clean up contents of upgrade directory beforehand.
		$upgrade_files = $wp_filesystem->dirlist($upgrade_folder);
		if ( !empty($upgrade_files) ) {
			foreach ( $upgrade_files as $file )
				$wp_filesystem->delete($upgrade_folder . $file['name'], true);
		}

		//We need a working directory
		$working_dir = $upgrade_folder . 'geo_dat/';

		// Clean up working directory
		if ( $wp_filesystem->is_dir($working_dir) )
			$wp_filesystem->delete($working_dir, true);

		if ( !$wp_filesystem->mkdir($working_dir, FS_CHMOD_DIR) )
			return new WP_Error('mkdir_failed', $this->strings['mkdir_failed'], $working_dir);

		$result = '';
		 $zh = gzopen($package,'r');
		 while ($line = gzgets($zh,1024)) {
				$result .= $line;
			}
		 gzclose($zh);

		if (false == $wp_filesystem->put_contents( $working_dir.$this->geofile, $result)) {
			$wp_filesystem->delete($working_dir, true);
			return new WP_Error('copy_failed', $this->strings['copy_failed'], $working_dir.$this->geofile);
		}

		// Once extracted, delete the package if required.
		if ( $delete_package )
			unlink($package);

		if ( is_wp_error($result) ) {
			$wp_filesystem->delete($working_dir, true);
			return $result;
		}

		return $working_dir;
	}

	function http_request_args($args) {
		$args['decompress'] = false;
		return $args;
	}

	function update($url) {

		$this->init();
		$this->update_strings();

		add_filter('http_request_args', array(&$this, 'http_request_args'));

		$this->run(array(
					'package' => $url,
					'destination' => SLIMSTATPATH . 'lib/geoip/data',
					'clear_destination' => true,
					'clear_working' => true,
					'hook_extra' => array()
				));

		remove_filter('http_request_args', array(&$this, 'http_request_args'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;
	}

	// modified parent::install_package cause we just update one file only and don't want to touch any other files or folders.
	function install_package($args = array()) {
		global $wp_filesystem;
		$defaults = array( 'source' => '', 'destination' => '', //Please always pass these
						'clear_destination' => false, 'clear_working' => false,
						'hook_extra' => array());

		$args = wp_parse_args($args, $defaults);
		extract($args);

		@set_time_limit( 300 );

		if ( empty($source) || empty($destination) )
			return new WP_Error('bad_request', $this->strings['bad_request']);

		$this->skin->feedback('installing_package');

		//Retain the Original source and destinations
		$remote_source = $source;
		$local_destination = $destination;

		$source_files = array_keys( $wp_filesystem->dirlist($remote_source) );
		$remote_destination = $wp_filesystem->find_folder($local_destination);

		if ( count($source_files) == 0 )
			return new WP_Error('bad_package', $this->strings['bad_package']); //There are no files?

		//If we're not clearing the destination folder, and something exists there allready, Bail.
		if ( ! $clear_destination && $wp_filesystem->exists($remote_destination.$this->geofile) ) {
			$wp_filesystem->delete($remote_source, true); //Clear out the source files.
			return new WP_Error('file_exists', $this->strings['file_exists'], $remote_destination.$this->geofile );
		} else if ( $clear_destination ) {
			//We're going to clear the destination if theres something there
			$this->skin->feedback('remove_old');

			$removed = true;
			if ( $wp_filesystem->exists($remote_destination.$this->geofile) )
				$removed = $wp_filesystem->delete($remote_destination.$this->geofile, true);

			if ( ! $removed )
				return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);
		}

		//Create destination if needed
		if ( !is_dir($destination) )
			if ( !$wp_filesystem->mkdir($remote_destination, FS_CHMOD_DIR) )
				return new WP_Error('mkdir_failed', $this->strings['mkdir_failed'], $remote_destination);

		// Copy new version of item into place.
		$result = copy_dir($source, $remote_destination);
		if ( is_wp_error($result) ) {
			if ( $clear_working )
				$wp_filesystem->delete($remote_source, true);
			return $result;
		}

		//Clear the Working folder?
		if ( $clear_working )
			$wp_filesystem->delete($remote_source, true);

		$destination_name = basename( str_replace($local_destination, '', $destination) );
		if ( '.' == $destination_name )
			$destination_name = '';

		$this->result = compact('local_source', 'source', 'source_name', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination', 'delete_source_dir');

		//Bombard the calling function will all the info which we've just used.
		return $this->result;
	}
}

class GeoIP_Upgrader_Skin extends WP_Upgrader_Skin {

	function header() {
		if ( $this->done_header )
			return;
		$this->done_header = true;
		echo '<div>';
		echo '<h4>' . $this->options['title'] . '</h4>';
	}

}
?>