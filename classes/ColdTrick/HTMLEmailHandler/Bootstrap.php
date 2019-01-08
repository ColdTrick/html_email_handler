<?php

namespace ColdTrick\HTMLEmailHandler;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::init()
	 */
	public function init() {
		
		$this->registerHooks();
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		// Handler that takes care of sending emails as HTML
		$hooks->registerHandler('email', 'system', __NAMESPACE__ . '\Email::emailHandler');
		$hooks->registerHandler('register', 'menu:theme_sandbox', __NAMESPACE__ . '\ThemeSandbox::menu');
	}
}
