<?php

namespace ColdTrick\HTMLEmailHandler;

class Cron {
	
	/**
	 * Cleanup the cached inline images
	 *
	 * @param string $hook   the name of the hook
	 * @param string $type   the type of the hook
	 * @param mixed  $return current return value
	 * @param array  $params supplied params
	 *
	 * @return void
	 */
	public static function imageCacheCleanup($hook, $type, $return, $params) {
		
		if (empty($params) || !is_array($params)) {
			return;
		}
		
		$cache_dir = elgg_get_data_path() . 'html_email_handler/image_cache/';
		if (!is_dir($cache_dir)) {
			return;
		}
		
		$dh = opendir($cache_dir);
		if (empty($dh)) {
			return;
		}
		
		$max_lifetime = elgg_extract('time', $params, time()) - (24 * 60 * 60);
		
		while (($filename = readdir($dh)) !== false) {
			// make sure we have a file
			if (!is_file($cache_dir . $filename)) {
				continue;
			}
		
			$modified_time = filemtime($cache_dir . $filename);
			if ($modified_time > $max_lifetime) {
				continue;
			}
		
			// file is past lifetime, so cleanup
			unlink($cache_dir . $filename);
		}
		
		closedir($dh);
	}
}
