<?php

use ColdTrick\HTMLEmailHandler\ImageFetcher;

$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('html_email_handler:settings:sendmail_options'),
	'#help' => elgg_echo('html_email_handler:settings:sendmail_options:description', [elgg_get_site_entity()->getEmailAddress()]),
	'name' => 'params[sendmail_options]',
	'value' => $plugin->sendmail_options,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('html_email_handler:settings:limit_subject'),
	'#help' => elgg_echo('html_email_handler:settings:limit_subject:subtext'),
	'name' => 'params[limit_subject]',
	'checked' => $plugin->limit_subject === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('html_email_handler:settings:embed_images'),
	'#help' => elgg_echo('html_email_handler:settings:embed_images:subtext'),
	'name' => 'params[embed_images]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'base64' => elgg_echo('html_email_handler:settings:embed_images:base64'),
		'attach' => elgg_echo('html_email_handler:settings:embed_images:attach'),
	],
	'value' => $plugin->embed_images,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('html_email_handler:settings:proxy_host'),
	'name' => 'params[proxy_host]',
	'value' => $plugin->proxy_host,
]);

echo elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('html_email_handler:settings:proxy_port'),
	'name' => 'params[proxy_port]',
	'value' => $plugin->proxy_port,
	'min' => 0,
	'max' => 65535,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('html_email_handler:settings:proxy_disable_ssl_verify'),
	'#help' => elgg_echo('html_email_handler:settings:proxy_disable_ssl_verify:help'),
	'name' => 'params[proxy_disable_ssl_verify]',
	'default' => 0,
	'value' => 1,
	'checked' => !empty($plugin->proxy_disable_ssl_verify),
	'switch' => true,
]);
