<?php

use ColdTrick\HTMLEmailHandler\Bootstrap;

require_once(__DIR__ . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'html_part' => 1,
	],
	'routes' => [
		'html_email_handler:test' => [
			'path' => '/html_email_handler/test',
			'resource' => 'html_email_handler/test',
			'middleware' => [
				\Elgg\Router\Middleware\AdminGatekeeper::class,
			],
		],
	],
];
