<?php

namespace ColdTrick\HTMLEmailHandler;

class DeveloperTools {
	
	public static function preventLogOutput(\Elgg\Hook $hook) {
		// prevent developer tools output
		elgg_register_plugin_hook_handler('view_vars', 'developers/log', '\Elgg\Values::preventViewOutput');
	}
	
	public static function reenableLogOutput(\Elgg\Hook $hook) {
		// re-enable developer tools output
		elgg_unregister_plugin_hook_handler('view_vars', 'developers/log', '\Elgg\Values::preventViewOutput');
	}
}
