<?php
/**
 * Main plugin file
 */

require_once(dirname(__FILE__) . '/lib/functions.php');
@include_once(dirname(__FILE__) . '/vendor/autoload.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'html_email_handler_init');

/**
 * Gets called during system initialization
 *
 * @return void
 */
function html_email_handler_init() {
	
	// register page_handler for nice URL's
	elgg_register_page_handler('html_email_handler', '\ColdTrick\HTMLEmailHandler\PageHandler::htmlEmailHandler');
	
	// plugin hooks
	elgg_register_plugin_hook_handler('cron', 'daily', '\ColdTrick\HTMLEmailHandler\Cron::imageCacheCleanup');
	// Handler that takes care of sending emails as HTML
	elgg_register_plugin_hook_handler('email', 'system', '\ColdTrick\HTMLEmailHandler\Email::email');
	elgg_register_plugin_hook_handler('register', 'menu:theme_sandbox', '\ColdTrick\HTMLEmailHandler\ThemeSandbox::menu');
	
}
