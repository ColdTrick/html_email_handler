<?php
/**
 * All plugin hook handlers are bundled here
 */

/**
 * Hook to handle emails send by elgg_send_email
 *
 * @param string $hook   'email'
 * @param string $type   'system'
 * @param bool   $return the current return value
 * @param array  $params supplied params containing:
 * 		to 		=> who to send the email to
 * 		from 	=> who is the sender
 * 		subject => subject of the message
 * 		body 	=> message
 * 		params 	=> optional params
 *
 * @return bool
 */
function html_email_handler_email_hook($hook, $type, $return, $params) {
	// generate HTML mail body
	$html_message = html_email_handler_make_html_body($params["subject"], $params["body"]);
	
	// set options for sending
	$options = array(
		"to" => $params["to"],
		"from" => $params["from"],
		"subject" => $params["subject"],
		"html_message" => $html_message,
		"plaintext_message" => $params["body"]
	);
	
	// Add optional attachments
	if ($params["attachments"]) {
		$options["attachments"] = $params["attachments"];
	}
	
	return html_email_handler_send_email($options);
}
