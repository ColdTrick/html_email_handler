<?php

namespace ColdTrick\HTMLEmailHandler;

class PageHandler {
	
	/**
	 * The page handler for html_email_handler
	 *
	 * @param array $page the page elements
	 *
	 * @return bool
	 */
	public static function htmlEmailHandler($page) {
		
		$pages_dir = elgg_get_plugins_path() . 'html_email_handler/pages/';
		$include_file = false;
		
		switch ($page[0]) {
			case 'test':
				$include_file = "{$pages_dir}test.php";
				break;
		}
		
		if (!empty($include_file)) {
			include($include_file);
			return true;
		}
		
		return false;
	}
}
