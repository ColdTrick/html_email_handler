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
                'html_email_handler:settings:smtp_server' => "Mail Server (SMTP) Name or IP:",
                'html_email_handler:settings:smtp_port' => "Mail Server (SMTP) port number:(default=25)",
                'html_email_handler:settings:smtp_server:description' => "Specify SMTP Server IP Address or name to use for sending emails. Leave blank to use sendmail.Default port is 25",
	        'html_email_handler:settings:smtp_usr' => "SMTP Username",
                'html_email_handler:settings:smtp_pwd' => "SMTP Password",
                'html_email_handler:settings:smtp_usrpwd:description' => "If left blank,it will be assumed that authentication is not required ",
		
		// notification body
		'html_email_handler:notification:footer:settings' => "Configure your notification settings %shere%s",
	);

	add_translation("en", $english);
