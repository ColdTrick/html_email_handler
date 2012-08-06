<?php 

	$english = array(
		'html_email_handler' => "HTML E-mail Handler",
		
		'html_email_handler:theme_preview:menu' => "HTML notification",
		
		// settings
		'html_email_handler:settings:notifications:description' => "When you enable this option all notifications to the users of your site will be in HTML format.",
		'html_email_handler:settings:notifications' => "Use as default e-mail notification handler",
		'html_email_handler:settings:notifications:subtext' => "This will send all outgoing e-mails as HTML mails",
		
		'html_email_handler:settings:sendmail_options' => "Additional parameters for use with sendmail (optional)",
		'html_email_handler:settings:fallback_email' => 'It is bad practice to use norepy@ - if possible, provide a real, alternative email address',
		'html_email_handler:settings:sendmail_options:description' => "Here you can configure additional setting when using sendmail, for example -f%s (to better prevent mails being marked as spam)",
		'html_email_handler:settings:fallback_email_options:description' => 'It is bad business practice not to provide a real email address that people can respond to. Here is an article discussing this topic <a href="http://www.netmagazine.com/opinions/why-noreply-email-addresses-are-bad-business">Why noreply addresses are bad for business</a>, therefore, it is recommended that you provide your users with a valid email which they can respond to.',
		
		// notification body
		'html_email_handler:notification:footer:settings' => "Configure your notification settings %shere%s",
		
	);

	add_translation("en", $english);
