<?php

namespace ColdTrick\HTMLEmailHandler;

class ThemeSandbox {
	
	/**
	 * Add a menu item to the themeing sandbox
	 *
	 * @param string          $hook         the name of the hook
	 * @param string          $type         the type of the hook
	 * @param \ElggMenuItem[] $return_value current return value
	 * @param array           $params       supplied params
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function menu($hook, $type, $return_value, $params) {
		
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'html_email_handler',
			'text' => elgg_echo('html_email_handler:theme_preview:menu'),
			'href' => 'html_email_handler/test',
			'target' => '_blank',
		]);
		
		return $return_value;
	}
}
