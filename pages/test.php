<?php
/**
 * A test page for theme developer to view the layout of an email notification
 */

elgg_admin_gatekeeper();

$user = elgg_get_logged_in_user_entity();

$subject = elgg_echo("useradd:subject");
$plain_message = elgg_echo("useradd:body");

$html_message = elgg_view("html_email_handler/notification/body", array(
	"subject" => $subject,
	"body" => $plain_message,
	"recipient" => $user
));

$html_message = html_email_handler_css_inliner($html_message);
$html_message_ext = html_email_handler_normalize_urls($html_message);
$html_message_ext = html_email_handler_base64_encode_images($html_message_ext);

echo $html_message_ext;

if (get_input("mail")) {
	$options = array(
		"to" => $user->email,
		"subject" => $subject,
		"plaintext_message" => $plain_message,
		"html_message" => $html_message
	);
	
	html_email_handler_send_email($options);
}