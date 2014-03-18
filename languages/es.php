<?php
	/* SPANISH by GEAR invent! */
	
	$spa = array(
		'html_email_handler' => "Manejador de E-mail con HMTL",
		
		'html_email_handler:theme_preview:menu' => "Notificaciones HTML",
		
		// settings - configuraciones
		
		'html_email_handler:settings:notifications:description' => "Cuando active esta opción, todas las notificaciones hacia los usuarios de su sitio web serán formateadas en HTML.",
		'html_email_handler:settings:notifications' => "Usar como el manejador de notificaciones de correo predeterminado",
		'html_email_handler:settings:notifications:subtext' => "Se enviarán todos los mails de salida con formato HTML",
		
		'html_email_handler:settings:sendmail_options' => "Parámetros adicionales de sendmail (opcionales)",
		'html_email_handler:settings:sendmail_options:description' => "Aquí podrás configurar opciones del software sendmail, por ejemplo -f %s (para prevenir mejor los mails detectados como spam)",
		
		// notification body - cuerpo del correo
		'html_email_handler:notification:footer:settings' => "Configurar los seteos de las configuraciones %saqui%s",
	
	// Object:notifications hooks control @TODO EN->ES
	'html_email_handler:settings:object_notifications_hook' => "Enable the hook on object:notifications",
	'html_email_handler:settings:object_notifications_hook:subtext' => "This hook lets other plugins easily add attachments and other parameters to notify_user, and therefor to emails, the same way messages can be changed. Caution because the use of this hook can break other notification plugins processes -at least advanced_notifications- because it handles the sending process, and replies \"true\" to the hook, which blocks the process when the hook is triggered.<br />If you don't know what to choose, leave on default.",
	
	);
	
	add_translation("es", $spa);
