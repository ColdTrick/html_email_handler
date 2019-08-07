<?php

namespace ColdTrick\HTMLEmailHandler;

class ThemeSandbox {
	
	/**
	 * Add a menu item to the themeing sandbox
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:theme_sandbox'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function menu(\Elgg\Hook $hook) {
		
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$return_value = $hook->getValue();
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'html_email_handler',
			'text' => elgg_echo('html_email_handler:theme_preview:menu'),
			'href' => 'html_email_handler/test',
			'target' => '_blank',
		]);
		
		return $return_value;
	}
}
