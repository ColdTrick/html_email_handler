<?php 

	require_once(dirname(__FILE__) . "/lib/functions.php");
	// include CSS coverter if needed
	if(!class_exists("Emogrifier")){
		require_once(dirname(__FILE__) . "/vendors/emogrifier/emogrifier.php");
	}

	function html_email_handler_init(){
		// do we need to overrule default email notifications
		if(get_plugin_setting("notifications", "html_email_handler") == "yes"){
			register_notification_handler("email", "html_email_handler_notification_handler");
		}
		
		register_page_handler('html_email_handler','html_email_handler_page_handler');
	}
	
	function html_email_handler_page_handler($page){
		
		switch ($page[0]) {
			case 'test':
				include(dirname(__FILE__) . "/pages/test.php");
				break;
		}
	}
	
	function html_email_handler_notification_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL){
		global $CONFIG;
	
		if (empty($from)) {
			throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'from'));
		}
	
		if (empty($to)) {
			throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'to'));
		}
		
		if(empty($message)){
			throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'message'));
		}
	
		if (empty($to->email)) {
			throw new NotificationException(sprintf(elgg_echo('NotificationException:NoEmailAddress'), $to->guid));
		}
		
		// To
		if(!empty($to->name)){
			$to = $to->name . " <" . $to->email . ">";
		} else {
			$to = $to->email;
		}
	
		// From
		// If there's an email address, use it - but only if its not from a user.
		if (!($from instanceof ElggUser) && !empty($from->email)) {
			if(!empty($from->name)){
				$from = $from->name . " <" . $from->email . ">";
			} else {
				$from = $from->email;
			}
		} elseif ($CONFIG->site && !empty($CONFIG->site->email)) {
			// Use email address of current site if we cannot use sender's email
			if(!empty($CONFIG->site->name)){
				$from = $CONFIG->site->name . " <" . $CONFIG->site->email . ">";
			} else {
				$from = $CONFIG->site->email;
			}
		} else {
			// If all else fails, use the domain of the site.
			if(!empty($CONFIG->site->name)){
				$from = $CONFIG->site->name . " <noreply@" . get_site_domain($CONFIG->site_guid) . ">";
			} else {
				$from = "noreply@" . get_site_domain($CONFIG->site_guid);
			}
		}
		
		// generate HTML mail body
		$html_message = elgg_view("html_email_handler/notification/body", array("title" => $subject, "message" => parse_urls($message)));
		if(defined("XML_DOCUMENT_NODE")){
			if($transform = html_email_handler_css_inliner($html_message)){
				$html_message = $transform;
			}
		}
	
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
	register_elgg_event_handler("init", "system", "html_email_handler_init");

?>