<?php 

$french = array(
	'html_email_handler' => "Envoi d'email en HTML",
	
	'html_email_handler:theme_preview:menu' => "Notification HTML",
	
	// settings
	'html_email_handler:settings:notifications:description' => "Lorsque vous activez cette option, toutes les notifications envoyées aux membres du site seront au format HTML (au lieu du texte brut).",
	'html_email_handler:settings:notifications' => "Utiliser comme gestionnaire d'email de notification par défaut",
	'html_email_handler:settings:notifications:subtext' => "Ceci va envoyer tous les mails sortant au format HTML",
	
	'html_email_handler:settings:sendmail_options' => "Paramètres additionnels pour sendmail (optionnel)",
	'html_email_handler:settings:sendmail_options:description' => "Vous pouvez configurer ici des paramètres additionnels lorsque vous utilisez sendmail, par exemple -f %s (pour mieux éviter que les mails soient marqués comme spam)",
	
	// notification body
	'html_email_handler:notification:footer:settings' => "Configurez vos notifications %sen cliquant sur ce lien%s",
	
	// Object:notifications hooks control
	'html_email_handler:settings:object_notifications_hook' => "Activer le hook sur object:notifications",
	'html_email_handler:settings:object_notifications_hook:subtext' => "Ce hook permet à d'autres plugins d'ajouter facilement des pièces jointes aux emails envoyés, de la même manière qu'ils peuvent modifier le contenu des messages. Attention car il peut causer des problèmes de compatibilité dans certains cas, en bloquant l'utilisation du hook par d'autres plugins -notamment advanced_notifications- car il prend en charge le processus d'envoi et répond donc \"true\" au hook.<br />Si vous ne savez pas quoi faire, laissez le réglage par défaut.",

);

add_translation("fr", $french);

