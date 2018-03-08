<?php

return [
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
