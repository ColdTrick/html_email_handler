<?php
/**
 * A test page for theme developer to view the layout of an email notification
 */

admin_gatekeeper();

$title = elgg_echo("useradd:subject");
$message = elgg_echo("useradd:body");

echo elgg_view("html_email_handler/notification/body", array(
	"subject" => $title,
	"body" => $message,
	"recipient" => elgg_get_logged_in_user_entity()
));
