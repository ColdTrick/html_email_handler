<?php
/**
 * All helpder functions for this plugin can be found here
 */

/**
 * This function sends out a full HTML mail. It can handle several options
 *
 * This function requires the options 'to' and ('html_message' or 'plaintext_message')
 *
 * @param array $options in the format:
 * 		to => STR|ARR of recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 * 		from => STR of senden in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 * 		subject => STR with the subject of the message
 * 		html_message => STR with the HTML version of the message
 * 		plaintext_message STR with the plaintext version of the message
 * 		cc => NULL|STR|ARR of CC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 * 		bcc => NULL|STR|ARR of BCC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 * 		date => NULL|UNIX timestamp with the date the message was created
 * 		attachments => NULL|ARR of array('mimetype', 'filename', 'content')
 *
 * @return bool
 */
function html_email_handler_send_email(array $options = null) {
	static $limit_subject;
	$result = false;
	
	$site = elgg_get_site_entity();
	
	// make site email
	if (!empty($site->email)) {
		$site_from = html_email_handler_make_rfc822_address($site);
	} else {
		// no site email, so make one up
		$site_from = "noreply@" . get_site_domain($site->getGUID());
		
		if (!empty($site->name)) {
			$site_name = $site->name;
			if (strstr($site_name, ",")) {
				$site_name = '"' . $site_name . '"'; // Protect the name with quotations if it contains a comma
			}
			
			$site_name = "=?UTF-8?B?" . base64_encode($site_name) . "?="; // Encode the name. If may content nos ASCII chars.
			$site_from = $site_name . " <" . $site_from . ">";
		}
	}
	
	$sendmail_options = html_email_handler_get_sendmail_options();
	
	if (!isset($limit_subject)) {
		$limit_subject = false;
		
		if (elgg_get_plugin_setting("limit_subject", "html_email_handler") == "yes") {
			$limit_subject = true;
		}
	}
	
	// set default options
	$default_options = array(
		"to" => array(),
		"from" => $site_from,
		"subject" => "",
		"html_message" => "",
		"plaintext_message" => "",
		"cc" => array(),
		"bcc" => array(),
		"date" => null,
	);
	
	// merge options
	$options = array_merge($default_options, $options);
	
	// check options
	if (!empty($options["to"]) && !is_array($options["to"])) {
		$options["to"] = array($options["to"]);
	}
	if (!empty($options["cc"]) && !is_array($options["cc"])) {
		$options["cc"] = array($options["cc"]);
	}
	if (!empty($options["bcc"]) && !is_array($options["bcc"])) {
		$options["bcc"] = array($options["bcc"]);
	}
	
	// can we send a message
	if (!empty($options["to"]) && (!empty($options["html_message"]) || !empty($options["plaintext_message"]))) {
		// start preparing
		// Facyla : better without spaces and special chars
		//$boundary = uniqid($site->name);
		$boundary = uniqid(elgg_get_friendly_title($site->name));
		
		// start building headers
		$headers = "";
		if (!empty($options["from"])) {
			$headers .= "From: " . $options["from"] . PHP_EOL;
		} else {
			$headers .= "From: " . $site_from . PHP_EOL;
		}
		
		// check CC mail
		if (!empty($options["cc"])) {
			$headers .= "Cc: " . implode(", ", $options["cc"]) . PHP_EOL;
		}
		
		// check BCC mail
		if (!empty($options["bcc"])) {
			$headers .= "Bcc: " . implode(", ", $options["bcc"]) . PHP_EOL;
		}
		
		// add a date header
		if (!empty($options["date"])) {
			$headers .= "Date: " . date("r", $options["date"]) . PHP_EOL;
		}
		
		$headers .= "X-Mailer: PHP/" . phpversion() . PHP_EOL;
		$headers .= "MIME-Version: 1.0" . PHP_EOL;
		
		// Facyla : try to add attchments if set
		$attachments = "";
		// Allow to add single or multiple attachments
		if (!empty($options["attachments"])) {
			
			$attachment_counter = 0;
			foreach ($options["attachments"] as $attachment) {
				
				// Alternatively fetch content based on a real file on server :
				// use $attachment["filepath"] to load file content in $attachment["content"]
				// @TODO : This has not been tested yet... careful !
				if (empty($attachment["content"]) && !empty($attachment["filepath"])) {
					$attachment["content"] = chunk_split(base64_encode(file_get_contents($attachment["filepath"])));
				}
				
				// Cannot attach an empty file in any case..
				if (empty($attachment["content"])) {
					continue;
				}
				
				// Count valid attachments
				$attachment_counter++;
				
				// Use defaults for other less critical settings
				if (empty($attachment["mimetype"])) {
					$attachment["mimetype"] = "application/octet-stream";
				}
				if (empty($attachment["filename"])) {
					$attachment["filename"] = "file_" . $attachment_counter;
				}
				
				$attachments .= "Content-Type: {" . $attachment["mimetype"] . "};" . PHP_EOL . " name=\"" . $attachment["filename"] . "\"" . PHP_EOL;
				$attachments .= "Content-Disposition: attachment;" . PHP_EOL . " filename=\"" . $attachment["filename"] . "\"" . PHP_EOL;
				$attachments .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;
				$attachments .= $attachment["content"] . PHP_EOL . PHP_EOL;
				$attachments .= "--mixed--" . $boundary . PHP_EOL;
			}
		}
		
		// Use attachments headers for real only if they are valid
		if (!empty($attachments)) {
			$headers .= "Content-Type: multipart/mixed; boundary=\"mixed--" . $boundary . "\"" . PHP_EOL . PHP_EOL;
		} else {
			$headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"" . PHP_EOL . PHP_EOL;
		}
		
		// start building the message
		$message = "";
		
		// TEXT part of message
		$plaintext_message = elgg_extract("plaintext_message", $options);
		if (!empty($plaintext_message)) {
			// normalize URL's in the text
			$plaintext_message = html_email_handler_normalize_urls($plaintext_message);
			
			// add boundry / content type
			$message .= "--" . $boundary . PHP_EOL;
			$message .= "Content-Type: text/plain; charset=\"utf-8\"" . PHP_EOL;
			$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;

			// add content
			$message .= chunk_split(base64_encode($plaintext_message)) . PHP_EOL . PHP_EOL;
		}
		
		// HTML part of message
		$html_message = elgg_extract("html_message", $options);
		if (!empty($html_message)) {
			// normalize URL's in the text
			$html_message = html_email_handler_normalize_urls($html_message);
			
			// add boundry / content type
			$message .= "--" . $boundary . PHP_EOL;
			$message .= "Content-Type: text/html; charset=\"utf-8\"" . PHP_EOL;
			$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;
			
			// add content
			$message .= chunk_split(base64_encode($html_message)) . PHP_EOL;
		}
		
		// Final boundry
		$message .= "--" . $boundary . "--" . PHP_EOL;
		
		// Facyla : FILE part of message
		if (!empty($attachments)) {
			// Build strings that will be added before TEXT/HTML message
			$before_message = "--mixed--" . $boundary . PHP_EOL;
			$before_message .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"" . PHP_EOL . PHP_EOL;
			
			// Build strings that will be added after TEXT/HTML message
			$after_message = PHP_EOL;
			$after_message .= "--mixed--" . $boundary . PHP_EOL;
			$after_message .= $attachments;
			
			// Wrap TEXT/HTML message into mixed message content
			$message = $before_message . PHP_EOL . $message . PHP_EOL . $after_message;
		}
		
		// convert to to correct format
		$to = implode(", ", $options["to"]);
		
		// encode subject to handle special chars
		$subject = $options["subject"];
		if ($limit_subject) {
			$subject = elgg_get_excerpt($subject, 175);
		}
		$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
		
		$result = mail($to, $subject, $message, $headers, $sendmail_options);
	}
	
	return $result;
}

/**
 * This function converts CSS to inline style, the CSS needs to be found in a <style> element
 *
 * @param string $html_text the html text to be converted
 *
 * @return false|string
 */
function html_email_handler_css_inliner($html_text) {
	$result = false;
	
	if (!empty($html_text) && defined("XML_DOCUMENT_NODE")) {
		$css = "";
		
		// set custom error handling
		libxml_use_internal_errors(true);
		
		$dom = new DOMDocument();
		$dom->loadHTML($html_text);
		
		$styles = $dom->getElementsByTagName("style");
		
		if (!empty($styles)) {
			$style_count = $styles->length;
			
			for ($i = 0; $i < $style_count; $i++) {
				$css .= $styles->item($i)->nodeValue;
			}
		}
		
		// clear error log
		libxml_clear_errors();
		
		elgg_load_library("emogrifier");
		
		$emo = new Pelago\Emogrifier($html_text, $css);
		$result = $emo->emogrify();
	}
	
	return $result;
}

/**
 * Make the HTML body from a $options array
 *
 * @param array  $options the options
 * @param string $body    the message body
 *
 * @return string
 */
function html_email_handler_make_html_body($options = "", $body = "") {
	global $CONFIG;
	
	if (!is_array($options)) {
		elgg_deprecated_notice("html_email_handler_make_html_body now takes an array as param, please update you're code", "1.9");
		
		$options = array(
			"subject" => $options,
			"body" => $body
		);
	}
	
	$defaults = array(
		"subject" => "",
		"body" => "",
		"language" => get_current_language()
	);
	
	$options = array_merge($defaults, $options);
	
	// in some cases when pagesetup isn't done yet this can cause problems
	// so manualy set is to done
	$unset = false;
	if (!isset($CONFIG->pagesetupdone)) {
		$unset = true;
		$CONFIG->pagesetupdone = true;
	}
	
	// generate HTML mail body
	$result = elgg_view("html_email_handler/notification/body", $options);
	
	// do we need to restore pagesetup
	if ($unset) {
		unset($CONFIG->pagesetupdone);
	}
	
	if (defined("XML_DOCUMENT_NODE")) {
		if ($transform = html_email_handler_css_inliner($result)) {
			$result = $transform;
		}
	}
	
	return $result;
}

/**
 * Get the plugin settings for sendmail
 *
 * @return string
 */
function html_email_handler_get_sendmail_options() {
	static $result;
	
	if (!isset($result)) {
		$result = "";
		
		$setting = elgg_get_plugin_setting("sendmail_options", "html_email_handler");
		if (!empty($setting)) {
			$result = $setting;
		}
	}
	
	return $result;
}

/**
 * This function build an RFC822 compliant address
 *
 * This function requires the option 'entity'
 *
 * @param ElggEntity $entity       entity to use as the basis for the address
 * @param bool       $use_fallback provides a fallback email if none defined
 *
 * @return string the correctly formatted address
 */
function html_email_handler_make_rfc822_address(ElggEntity $entity, $use_fallback = true) {
	// get the email address of the entity
	$email = $entity->email;
	if (empty($email) && $use_fallback) {
		// no email found, fallback to site email
		$site = elgg_get_site_entity();
		
		$email = $site->email;
		if (empty($email)) {
			// no site email, default to noreply
			$email = "noreply@" . get_site_domain($site->getGUID());
		}
	}
	
	// build the RFC822 format
	if (!empty($entity->name)) {
		$name = $entity->name;
		if (strstr($name, ",")) {
			$name = '"' . $name . '"'; // Protect the name with quotations if it contains a comma
		}
		
		$name = "=?UTF-8?B?" . base64_encode($name) . "?="; // Encode the name. If may content nos ASCII chars.
		$email = $name . " <" . $email . ">";
	}
	
	return $email;
}

/**
 * Normalize all URL's in the text to full URL's
 *
 * @param string $text the text to check for URL's
 *
 * @return string
 */
function html_email_handler_normalize_urls($text) {
	static $pattern = '/\s(?:href|src)=([\'"]\S+[\'"])/i';
	
	if (empty($text)) {
		return $text;
	}
	
	// find all matches
	$matches = array();
	preg_match_all($pattern, $text, $matches);
	
	if (empty($matches) || !isset($matches[1])) {
		return $text;
	}
	
	// go through all the matches
	$urls = $matches[1];
	$urls = array_unique($urls);
	
	foreach ($urls as $url) {
		// remove wrapping quotes from the url
		$real_url = substr($url, 1, -1);
		// normalize url
		$new_url = elgg_normalize_url($real_url);
		// make the correct replacement string
		$replacement = str_replace($real_url, $new_url, $url);
	
		// replace the url in the content
		$text = str_replace($url, $replacement, $text);
	}
	
	return $text;
}
