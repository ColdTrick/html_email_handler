<?php

namespace ColdTrick\HTMLEmailHandler;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::boot()
	 */
	public function boot() {
		$this->setSendmailParams();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::init()
	 */
	public function init() {
		$this->registerHooks();
	}
	
	/**
	 * Register plugin hooks
	 *
	 * @return void
	 */
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		// Handler that takes care of sending emails as HTML
		$hooks->registerHandler('prepare', 'system:email', __NAMESPACE__ . '\Email::limitSubjectLength');
		$hooks->registerHandler('register', 'menu:theme_sandbox', __NAMESPACE__ . '\ThemeSandbox::menu');
		$hooks->registerHandler('view', 'html_email_handler/notification/body', __NAMESPACE__ . '\DeveloperTools::reenableLogOutput');
		$hooks->registerHandler('view_vars', 'html_email_handler/notification/body', __NAMESPACE__ . '\DeveloperTools::preventLogOutput');
		$hooks->registerHandler('zend:message', 'system:email', __NAMESPACE__ . '\Email::makeHtmlMail');
	}
	
	/**
	 * Apply additional sendmail params to mailer
	 *
	 * @return void
	 */
	protected function setSendmailParams() {
		
		$params = $this->plugin->getSetting('sendmail_options');
		if (empty($params)) {
			return;
		}
		
		$mailer = _elgg_services()->mailer;
		if (!$mailer instanceof \Zend\Mail\Transport\Sendmail) {
			// using different mail handler
			return;
		}
		
		$mailer->setParameters($params);
	}
}
