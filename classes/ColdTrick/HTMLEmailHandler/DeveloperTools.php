<?php

namespace ColdTrick\HTMLEmailHandler;

use Elgg\ViewsService;

class DeveloperTools {
	
	/**
	 * Prevent the developer log output
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'html_email_handler/notification/body'
	 *
	 * @return void
	 */
	public static function preventLogOutput(\Elgg\Hook $hook) {
		// prevent developer tools output
		elgg_register_plugin_hook_handler('view_vars', 'developers/log', self::class . '::blockOutput');
	}
	
	/**
	 * Re-enable developer log output
	 *
	 * @param \Elgg\Hook $hook 'view', 'html_email_handler/notification/body'
	 *
	 * @return void
	 */
	public static function reenableLogOutput(\Elgg\Hook $hook) {
		// re-enable developer tools output
		elgg_unregister_plugin_hook_handler('view_vars', 'developers/log', self::class . '::blockOutput');
	}
	
	/**
	 * Prevent view output
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'developers/log'
	 *
	 * @return array
	 */
	public static function blockOutput(\Elgg\Hook $hook) {
		
		$vars = $hook->getValue();
		$vars[ViewsService::OUTPUT_KEY] = '';
		
		return $vars;
	}
}
