<?php 

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");
	
	function html_email_handler_init(){
		// do we need to overrule default email notifications
		if(elgg_get_plugin_setting("notifications", "html_email_handler") == "yes"){
			// notification handler for nice From part
			register_notification_handler("email", "html_email_handler_notification_handler");
			
			// register hook to handle the rest of the email being send
			elgg_register_plugin_hook_handler("email", "system", "html_email_handler_email_hook");
		}
		
		// register page_handler for nice URL's
		elgg_register_page_handler("html_email_handler", "html_email_handler_page_handler");
		
		// register html converter library
		elgg_register_library("emogrifier", dirname(__FILE__) . "/vendors/emogrifier/emogrifier.php");
	}
	
	function html_email_handler_pagesetup(){
		elgg_register_menu_item('page', array(
			"name" => "html_email_handler",
			"text" => elgg_echo("html_email_handler:theme_preview:menu"),
			"href" => "html_email_handler/test",
			"context" => "theme_preview"
		));
	}

	function html_email_handler_page_handler($page){
		$result = false;

		switch ($page[0]) {
			case "test":
				$result = true;
				include(dirname(__FILE__) . "/pages/test.php");
				break;
		}

		return $result;
	}

	function html_email_handler_notification_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL){
		global $CONFIG;

		if (!$from) {
			$msg = elgg_echo("NotificationException:MissingParameter", array("from"));
			throw new NotificationException($msg);
		}

		if (!$to) {
			$msg = elgg_echo("NotificationException:MissingParameter", array("to"));
			throw new NotificationException($msg);
		}

		if ($to->email == "") {
			$msg = elgg_echo("NotificationException:NoEmailAddress", array($to->guid));
			throw new NotificationException($msg);
		}

		// To
		$to = html_email_handler_make_rfc822_address($to);

		// From
		if($dedicated_email = elgg_get_plugin_setting('dedicated_email_option')){
			// Use dedicated from email address, if one is set e.g. notifications@sitename.com
		    $from = $from->name . " <" . $dedicated_email . ">";
		}elseif(!($from instanceof ElggUser) && !empty($from->email)) {
			// If there's an email address, use it - but only if its not from a user.
			$from = html_email_handler_make_rfc822_address($from);
		} elseif ($CONFIG->site && !empty($CONFIG->site->email)) {
		    // Use email address of current site if we cannot use sender's email
		    $from = html_email_handler_make_rfc822_address($CONFIG->site);
		} else {
			// If all else fails, use the domain of the site.
			if(!empty($CONFIG->site->name)){
				$from = $CONFIG->site->name . " <noreply@" . get_site_domain($CONFIG->site_guid) . ">";
			} else {
				$from = "noreply@" . get_site_domain($CONFIG->site_guid);
			}
		}
		
		// generate HTML mail body
		$html_message = html_email_handler_make_html_body($subject, $message);
	
		// set options for sending
		$options = array(
			"to" => $to,
			"from" => $from,
			"subject" => $subject,
			"html_message" => $html_message,
			"plaintext_message" => $message
		);
		
		return html_email_handler_send_email($options);
	}

	// register default Elgg events
	elgg_register_event_handler("init", "system", "html_email_handler_init");
	elgg_register_event_handler("pagesetup", "system", "html_email_handler_pagesetup");
