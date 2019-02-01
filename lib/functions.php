<?php
/**
 * All helpder functions for this plugin can be found here
 */

use ColdTrick\HTMLEmailHandler\ImageFetcher;

/**
 * Sends out a full HTML mail
 *
 * @param array $options In the format:
 *     to => STR|ARR of recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 *     from => STR of senden in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 *     subject => STR with the subject of the message
 *     body => STR with the message body
 *     plaintext_message STR with the plaintext version of the message
 *     html_message => STR with the HTML version of the message
 *     cc => NULL|STR|ARR of CC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 *     bcc => NULL|STR|ARR of BCC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
 *     date => NULL|UNIX timestamp with the date the message was created
 *     attachments => NULL|ARR of array(array('mimetype', 'filename', 'content'))
 *
 * @deprecated use elgg_send_email()
 * @return bool
 */
function html_email_handler_send_email(array $options = null) {
	static $limit_subject;
	return false;
	
	$site = elgg_get_site_entity();
		
	// set default options
	$default_options = array(
		"to" => array(),
		"subject" => "",
		"html_message" => "",
		"plaintext_message" => "",
		"cc" => array(),
		"bcc" => array(),
		"date" => time(),
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
	
	if (empty($options['html_message']) && empty($options['plaintext_message'])) {
		$options['html_message'] = html_email_handler_make_html_body($options);
		$options['plaintext_message'] = $options['body'];
	}
	
	// can we send a message
	if (empty($options["to"]) || (empty($options["html_message"]) && empty($options["plaintext_message"]))) {
		return false;
	}
	
	// start preparing
	// Facyla : better without spaces and special chars
	//$boundary = uniqid($site->name);
	$boundary = uniqid(elgg_get_friendly_title($site->name));
	
	$headers = elgg_extract('headers', $options, []);
	
	// start building headers
	if (!empty($options["from"])) {
		$headers['From'] = $options['from'];
	} else {
		$headers['From'] = $site_from;
	}
	
	// check CC mail
	if (!empty($options["cc"])) {
		$headers['Cc'] = implode(', ', $options['cc']);
	}
	
	// check BCC mail
	if (!empty($options["bcc"])) {
		$headers['Bcc'] = implode(', ', $options['bcc']);
	}
	
	// add a date header
	if (!empty($options["date"])) {
		$headers['Date'] = date('r', $options['date']);
	}
	
	$headers['X-Mailer'] = ' PHP/' . phpversion();
	$headers['MIME-Version'] = '1.0';
	
	// Facyla : try to add attchments if set
	$attachments = "";
	// Allow to add single or multiple attachments
	if (!empty($options["attachments"])) {
		
		$attachment_counter = 0;
		foreach ($options["attachments"] as $attachment) {
			
			// Alternatively fetch content based on an absolute path to a file on server:
			if (empty($attachment["content"]) && !empty($attachment["filepath"])) {
				if (is_file($attachment["filepath"])) {
					$attachment["content"] = chunk_split(base64_encode(file_get_contents($attachment["filepath"])));
				}
			}
			
			// Cannot attach an empty file in any case..
			if (!elgg_extract('content', $attachment)) {
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
		$headers['Content-Type'] = "multipart/mixed; boundary=\"mixed--{$boundary}\"";
	} else {
		$headers['Content-Type'] = "multipart/alternative; boundary=\"{$boundary}\"";
	}
	
	$header_eol = "\r\n";
	if (elgg_get_config('broken_mta')) {
		// Allow non-RFC 2822 mail headers to support some broken MTAs
		$header_eol = "\n";
	}
	
	// stringify headers
	$headers_string = '';
	foreach ($headers as $key => $value) {
		$headers_string .= "$key: $value{$header_eol}";
	}
	
	// start building the message
	$message = "";
	
	// TEXT part of message
	$plaintext_message = elgg_extract("plaintext_message", $options);
	if (!empty($plaintext_message)) {
		// strip html tags from message
		$plaintext_message = elgg_strip_tags($plaintext_message);
		
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
		$html_boundary = $boundary;
		
		// normalize URL's in the text
		$html_message = html_email_handler_normalize_urls($html_message);
		$html_message = html_email_handler_base64_encode_images($html_message);
		
		$image_attachments = html_email_handler_attach_images($html_message);
		if (is_array($image_attachments)) {
			$html_boundary .= "-alt";
			$html_message = elgg_extract("text", $image_attachments);
			
			$message .= "--" . $boundary . PHP_EOL;
			$message .= "Content-Type: multipart/related; boundary=\"$html_boundary\"" . PHP_EOL . PHP_EOL;
		}
		
		// add boundry / content type
		$message .= "--" . $html_boundary . PHP_EOL;
		$message .= "Content-Type: text/html; charset=\"utf-8\"" . PHP_EOL;
		$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;
		
		// add content
		$message .= chunk_split(base64_encode($html_message)) . PHP_EOL;
		
		if (is_array($image_attachments)) {
			$images = elgg_extract("images", $image_attachments);
			
			foreach ($images as $image_info) {
				$message .= "--" . $html_boundary . PHP_EOL;
				$message .= "Content-Type: " . elgg_extract("content_type", $image_info) . "; charset=\"utf-8\"" . PHP_EOL;
				$message .= "Content-Disposition: inline; filename=\"" . elgg_extract("name", $image_info) . "\"" . PHP_EOL;
				$message .= "Content-ID: <" . elgg_extract("uid", $image_info) . ">" . PHP_EOL;
				$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;
				
				// add content
				$message .= chunk_split(elgg_extract("data", $image_info)) . PHP_EOL;
			}
			
			$message .= "--" . $html_boundary . "--" . PHP_EOL;
		}
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
	$subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8'); // Decode any html entities
	$subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
	
	return mail($to, $subject, $message, $headers_string);
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
		
		$emo = new Pelago\Emogrifier($html_text, $css);
		$result = $emo->emogrify();
	}
	
	return $result;
}

/**
 * Make the HTML body from a $options array
 *
 * @param array $options the options, supports:
 * 	- subject: the subject of the e-mail
 * 	- body: the body of the e-mail
 * 	- language: the language of the e-mail (default: current language)
 *
 * @return string
 */
function html_email_handler_make_html_body(array $options = []) {
	$defaults = [
		'subject' => '',
		'body' => '',
		'language' => get_current_language()
	];
	
	$options = array_merge($defaults, $options);
	
	$options['body'] = elgg()->html_formatter->formatBlock($options['body']);
	
	// generate HTML mail body
	$result = elgg_view('html_email_handler/notification/body', $options);
	
	if (defined('XML_DOCUMENT_NODE')) {
		if ($transform = html_email_handler_css_inliner($result)) {
			$result = $transform;
		}
	}
	
	return $result;
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
	$matches = [];
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

/**
 * Convert images to inline images
 *
 * This can be enabled with a plugin setting (default: off)
 *
 * @param string $text the text of the message to embed the images from
 *
 * @return string
 */
function html_email_handler_base64_encode_images($text) {
	
	if (empty($text) || elgg_get_plugin_setting('embed_images', 'html_email_handler') !== 'base64') {
		return $text;
	}
	
	$image_urls = html_email_handler_find_images($text);
	if (empty($image_urls)) {
		return $text;
	}
	
	$fetcher = new ImageFetcher();
	
	foreach ($image_urls as $url) {
		// remove wrapping quotes from the url
		$image_url = substr($url, 1, -1);
		
		// get the image contents
		$contents = $fetcher->getImageBase64($image_url);
		if (empty($contents)) {
			continue;
		}
		
		// build inline image
		$replacement = str_replace($image_url, "data:{$contents}", $url);
		
		// replace in text
		$text = str_replace($url, $replacement, $text);
	}
	
	return $text;
}

/**
 * Find img src's in text
 *
 * @param string $text the text to search though
 *
 * @return false|array
 */
function html_email_handler_find_images($text) {
	static $pattern = '/\ssrc=([\'"]\S+[\'"])/i';
	
	if (empty($text)) {
		return false;
	}
	
	// find all matches
	$matches = [];
	preg_match_all($pattern, $text, $matches);
	
	if (empty($matches) || !isset($matches[1])) {
		return false;
	}
	
	// return all the found image urls
	return array_unique($matches[1]);
}

/**
 * Get information needed for attaching the images to the e-mail
 *
 * @param string $text the html text to search images in
 *
 * @return string|array
 */
function html_email_handler_attach_images($text) {
	
	if (empty($text) || elgg_get_plugin_setting('embed_images', 'html_email_handler') !== 'attach') {
		return $text;
	}
	
	// get images
	$image_urls = html_email_handler_find_images($text);
	if (empty($image_urls)) {
		return $text;
	}
	
	$fetcher = new ImageFetcher();
	
	$result = [
		'images' => [],
	];
	
	foreach ($image_urls as $url) {
		// remove wrapping quotes from the url
		$image_url = substr($url, 1, -1);
		
		// get the image data
		$image_data = $fetcher->getImage($image_url);
		if (empty($image_data)) {
			continue;
		}
		
		// Unique ID
		$uid = uniqid();
		
		$image_data['uid'] = $uid;
		
		$result['images'][] = $image_data;
		
		// replace url in the text with uid
		$replacement = str_replace($image_url, "cid:{$uid}", $url);
		
		$text = str_replace($url, $replacement, $text);
	}
	
	// return new text
	$result['text'] = $text;
	
	// return result
	return $result;
}
