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
		$hooks->registerHandler('register', 'menu:theme_sandbox', __NAMESPACE__ . '\ThemeSandbox::menu');
		$hooks->registerHandler('zend:message', 'system:email', __NAMESPACE__ . '\Email::makeHtmlMail');
	}
}
