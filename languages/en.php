<?php 

	$english = array(
		'html_email_handler' => "HTML E-mail Handler",
		
		'html_email_handler:theme_preview:menu' => "HTML notification",
		
		// settings
		'html_email_handler:settings:notifications:description' => "When you enable this option all notifications to the users of your site will be in HTML format.",
		'html_email_handler:settings:notifications' => "Use as default e-mail notification handler",
		'html_email_handler:settings:notifications:subtext' => "This will send all outgoing e-mails as HTML mails",
		
		'html_email_handler:settings:sendmail_options' => "Additional parameters for use with sendmail (optional)",
		'html_email_handler:settings:sendmail_options:description' => "Here you can configure additional setting when using sendmail, for example -f %s (to better prevent mails being marked as spam)",
		
		// notification body
		'html_email_handler:notification:footer:settings' => "Configure your notification settings %shere%s",
		
	// Object:notifications hooks control
	'html_email_handler:settings:object_notifications_hook' => "Enable the hook on object:notifications",
	'html_email_handler:settings:object_notifications_hook:subtext' => "This hook lets other plugins easily add attachments and other parameters to notify_user, and therefor to emails, the same way messages can be changed. Caution because the use of this hook can break other notification plugins processes -at least advanced_notifications- because it handles the sending process, and replies \"true\" to the hook, which blocks the process when the hook is triggered.<br />If you don't know what to choose, leave on default.",
	
	);

	add_translation("en", $english);
