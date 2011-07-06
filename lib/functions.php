<?php 

	/**
	 * 
	 * This function sends out a full HTML mail. It can handle several options
	 * 
	 * This function requires the options 'to' and ('html_message' or 'plaintext_message')
	 * 
	 * @param $options Array in the format:
	 * 		to => STR|ARR of recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 * 		from => STR of senden in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 * 		subject => STR with the subject of the message
	 * 		html_message => STR with the HTML version of the message
	 * 		plaintext_message STR with the plaintext version of the message
	 * 		cc => NULL|STR|ARR of CC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 * 		bcc => NULL|STR|ARR of BCC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 * 
	 * @return BOOL true|false
	 */
	function html_email_handler_send_email(array $options = null){
		global $CONFIG;
		$result = false;
		
		// make site email
		if(!empty($CONFIG->site->email)){
			if(!empty($CONFIG->site->name)){
				$site_from = $CONFIG->site->name . " <" . $CONFIG->site->email . ">";
			} else {
				$site_from = $CONFIG->site->email;
			}
		} else {
			// no site email, so make one up
			if(!empty($CONFIG->site->name)){
				$site_from = $CONFIG->site->name . " <noreply@" . get_site_domain($CONFIG->site_guid) . ">";
			} else {
				$site_from = "noreply@" . get_site_domain($CONFIG->site_guid);
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
			"bcc" => array()
		);
		
		// merge options
		$options = array_merge($default_options, $options);
		
		// check options
		if(!empty($options["to"]) && !is_array($options["to"])){
			$options["to"] = array($options["to"]);
		}
		if(!empty($options["cc"]) && !is_array($options["cc"])){
			$options["cc"] = array($options["cc"]);
		}
		if(!empty($options["bcc"]) && !is_array($options["bcc"])){
			$options["bcc"] = array($options["bcc"]);
		}
		
		// can we send a message
		if(!empty($options["to"]) && (!empty($options["html_message"]) || !empty($options["plaintext_message"]))){
			// start preparing
			$boundary = uniqid($CONFIG->site->name);
			
			// start building headers
			if(!empty($options["from"])){
				$headers .= "From: " . $options["from"] . PHP_EOL;
			} else {
				$headers .= "From: " . $site_from . PHP_EOL;
			}
			$headers .= "X-Mailer: PHP/" . phpversion() . PHP_EOL;
			$headers .= "MIME-Version: 1.0" . PHP_EOL;
			$headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"" . PHP_EOL . PHP_EOL;

			// check CC mail
			if(!empty($options["cc"])){
				$headers .= "Cc: " . implode(", ", $options["cc"]) . PHP_EOL;
			}
			
			// check BCC mail
			if(!empty($options["bcc"])){
				$headers .= "Bcc: " . implode(", ", $options["bcc"]) . PHP_EOL;
			}
			
			// TEXT part of message
			if(!empty($options["plaintext_message"])){
				$message .= "--" . $boundary . PHP_EOL;
				$message .= "Content-Type: text/plain; charset=\"utf-8\"" . PHP_EOL;
				$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL; 
				
				$message .= chunk_split(base64_encode($options["plaintext_message"])) . PHP_EOL . PHP_EOL;
			}
			
			// HTML part of message
			if(!empty($options["html_message"])){
				$message .= "--" . $boundary . PHP_EOL;
				$message .= "Content-Type: text/html; charset=\"utf-8\"" . PHP_EOL;
				$message .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL;
				
				$message .= chunk_split(base64_encode($options["html_message"])) . PHP_EOL;
			}
			
			// Final boundry
			$message .= "--" . $boundary . "--" . PHP_EOL;
			
			// convert to to correct format
			$to = implode(", ", $options["to"]);
			$result = mail($to, $options["subject"], $message, $headers);
		}			
		
		return $result;
	}
	
	/**
	 * This function converts CSS to inline style, the CSS needs to be found in a <style> element
	 * 
	 * @param $html_text => STR with the html text to be converted
	 * @return false | converted html text
	 */
	function html_email_handler_css_inliner($html_text){
		$result = false;
		
		if(!empty($html_text) && defined("XML_DOCUMENT_NODE")){
			$css = "";
			
			$dom = new DOMDocument();
			$dom->loadHTML($html_text);
			
			$styles = $dom->getElementsByTagName("style");
			
			if(!empty($styles)){
				$style_count = $styles->length;
				
				for($i = 0; $i < $style_count; $i++){
					$css .= $styles->item($i)->nodeValue;
				}
			}
			
			$emo = new Emogrifier($html_text, $css);
			$result = $emo->emogrify();
		}
		
		return $result;
	}

?>