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
		if($dedicated_email = elgg_get_plugin_setting('dedicated_email_option')){
			// Use dedicated from email address, if one is set e.g. notifications@sitename.com
		    $site_from = $CONFIG->site->name . " <" . $dedicated_email . ">";
		}elseif(!empty($CONFIG->site->email)){
			$sendmail_from = $CONFIG->site->email;
			$site_from = $sendmail_from;

			if(!empty($CONFIG->site->name)){
				$site_from = $CONFIG->site->name . " <" . $sendmail_from . ">";
			}
		} else {
			// no site email, so make one up
			$sendmail_from = "noreply@" . get_site_domain($CONFIG->site_guid);
			$site_from = $sendmail_from;

			if(!empty($CONFIG->site->name)){
				$site_from = $CONFIG->site->name . " <" . $sendmail_from . ">";
			}
		}
		
		$sendmail_options = html_email_handler_get_sendmail_options();
		
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
			$headers = "";
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

			// start building the message
			$message = "";

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
			$result = mail($to, $options["subject"], $message, $headers, $sendmail_options);
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

			// set custom error handling
			libxml_use_internal_errors(true);

			$dom = new DOMDocument();
			$dom->loadHTML($html_text);

			$styles = $dom->getElementsByTagName("style");

			if(!empty($styles)){
				$style_count = $styles->length;

				for($i = 0; $i < $style_count; $i++){
					$css .= $styles->item($i)->nodeValue;
				}
			}

			// clear error log
			libxml_clear_errors();

			elgg_load_library("emogrifier");

			$emo = new Emogrifier($html_text, $css);
			$result = $emo->emogrify();
		}

		return $result;
	}

	function html_email_handler_make_html_body($subject = "", $body = ""){
		// generate HTML mail body
		$result = elgg_view("html_email_handler/notification/body", array("title" => $subject, "message" => parse_urls($body)));

		if(defined("XML_DOCUMENT_NODE")){
			if($transform = html_email_handler_css_inliner($result)){
				$result = $transform;
			}
		}

		return $result;
	}

	function html_email_handler_get_sendmail_options(){
		static $result;

		if(!isset($result)){
			$result = "";

			if(($setting = elgg_get_plugin_setting("sendmail_options", "html_email_handler")) && !empty($setting)){
				$result = $setting;
			}
		}

		return $result;
	}

	/**
	 *
	 * This function build an RFC822 compliant address
	 *
	 * This function requires the option 'entity'
	 *
	 * @param ElggEntity $entity entity to use as the basis for the address
	 *
	 * @return string with the correctly formatted address
	 */
	function html_email_handler_make_rfc822_address(ElggEntity $entity) {
		if(!empty($entity->name)){
		    $name = $entity->name;
		    if (strstr($name, ',')) {
		        $name = '"' . $name . '"'; // Protect the name with quotations if it contains a comma
		    }
			$addr = $name . " <" . $entity->email . ">";
		} else {
			$addr = $entity->email;
		}

		return $addr;
	}
