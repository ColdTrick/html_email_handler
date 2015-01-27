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
	$html_message = html_email_handler_make_html_body($params);
	
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

/**
 * Send an email notification
 *
 * @param string $hook   Hook name
 * @param string $type   Hook type
 * @param bool   $return Has anyone sent a message yet?
 * @param array  $params Hook parameters
 *
 * @return bool
 */
function html_email_handler_send_email_notifications_hook($hook, $type, $return, $params) {
	
	if (empty($params) || !is_array($params)) {
		return false;
	}
	
	$message = elgg_extract("notification", $params);
	if (empty($message) || !($message instanceof Elgg_Notifications_Notification)) {
		return false;
	}
	
	$sender = $message->getSender();
	$recipient = $message->getRecipient();
	
	if (!$sender) {
		return false;
	}
	
	if (!$recipient || !$recipient->email) {
		return false;
	}
	
	$to = html_email_handler_make_rfc822_address($recipient);
	
	$site = elgg_get_site_entity();
	// If there's an email address, use it - but only if it's not from a user.
	if (!($sender instanceof ElggUser) && $sender->email) {
		$from = html_email_handler_make_rfc822_address($sender);
	} else if ($site->email) {
		$from = html_email_handler_make_rfc822_address($site);
	} else {
		// If all else fails, use the domain of the site.
		if (!empty($site->name)) {
			$name = $site->name;
			if (strstr($name, ",")) {
				$name = '"' . $name . '"'; // Protect the name with quotations if it contains a comma
			}
			
			$name = "=?UTF-8?B?" . base64_encode($name) . "?="; // Encode the name. If may content nos ASCII chars.
			$from = $name . " <noreply@" . $site->getDomain() . ">";
		} else {
			$from = "noreply@" . $site->getDomain();
		}
	}
	
	// generate HTML mail body
	$html_message = html_email_handler_make_html_body(array(
		"subject" => $message->subject,
		"body" => $message->body,
		"language" => $message->language,
		"recipient" => $recipient
	));
	
	// set options for sending
	$options = array(
		"to" => $to,
		"from" => $from,
		"subject" => $message->subject,
		"html_message" => $html_message,
		"plaintext_message" => $message->body
	);
	
	if (!empty($params) && is_array($params)) {
		$options = array_merge($options, $params);
	}
	
	return html_email_handler_send_email($options);
}
