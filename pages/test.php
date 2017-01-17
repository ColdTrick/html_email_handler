<?php
/**
 * A test page for theme developer to view the layout of an email notification
 */

elgg_admin_gatekeeper();

$user = elgg_get_logged_in_user_entity();
$site = elgg_get_site_entity();

$subject = elgg_echo('useradd:subject');
$plain_message = elgg_echo('useradd:body', [
	$user->name,
	$site->name,
	$site->url,
	$user->username,
	'test123',
]);

$html_message = html_email_handler_make_html_body([
	'subject' => $subject,
	'body' => $plain_message,
	'recipient' => $user,
]);

if (!empty($html_message)) {
	$html_message = html_email_handler_normalize_urls($html_message);
	$html_message = html_email_handler_base64_encode_images($html_message);
}

echo $html_message;

if (get_input('mail')) {
	// Test sending a basic HTML mail
	$options = [
		'to' => $user->email,
		'subject' => $subject,
		'body' => $plain_message,
		'recipient' => $user,
		'attachments' => [
			[
				'filepath' => dirname(__DIR__) . '/manifest.xml',
				'filename' => 'manifest.xml',
				'mimetype' => 'application/xml',
			],
		],
	];

	html_email_handler_send_email($options);

	// Test sending attachments through notify_user()
	$to = $user->guid;
	$from = $site->guid;
	$subject = 'Notification test';
	$message = 'This notification has been sent using notify_user() and it should have an attachment.';
	$params = [
		'recipient' => $user,
		'attachments' => [
			[
				'filepath' => dirname(__DIR__) . '/manifest.xml',
				'filename' => 'manifest.xml',
				'mimetype' => 'application/xml',
			],
		],
	];

	notify_user($to, $from, $subject, $message, $params, ['email']);
}
