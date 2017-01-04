<?php 

return array(
	'html_email_handler' => "Envoi d'email en HTML",
	
	'html_email_handler:theme_preview:menu' => "Notification HTML",
	
	// settings
	'html_email_handler:settings:notifications:description' => "Lorsque vous activez cette option, toutes les notifications envoyées aux membres du site seront au format HTML (au lieu de texte brut). Une version alternative en texte brut est également envoyée.",
	'html_email_handler:settings:notifications' => "Utiliser comme gestionnaire d'email de notification par défaut",
	'html_email_handler:settings:notifications:subtext' => "Ceci va envoyer tous les mails sortant au format HTML",
	'html_email_handler:settings:limit_subject' => "Limiter la longueur maximale du sujet des emails",
	'html_email_handler:settings:limit_subject:subtext' => "Activer cette option si les membres se plaignent de sujets d'emails illisibles (principalement des utilisateurs d'Outlook). Ceci va potentiellement faire perdre quelques informations dans le sujet.",
	'html_email_handler:settings:embed_images' => "Intégrer les images dans les emails",
	'html_email_handler:settings:embed_images:base64' => "Encodage en Base64",
	'html_email_handler:settings:embed_images:attach' => "Pièces jointes",
	'html_email_handler:settings:embed_images:subtext' => "Si activé, toutes les images seront intégrées dans les emails. Tous les clients de messagerie ne supportent pas les différentes options, veuillez tester l'option choisie.",
	'html_email_handler:settings:proxy_host' => "Hôte du proxy pour intégrer les images",
	'html_email_handler:settings:proxy_port' => "Numéro de port du proxy pour intégrer des images",
	
	'html_email_handler:settings:sendmail_options' => "Paramètres additionnels pour sendmail (optionnel)",
	'html_email_handler:settings:sendmail_options:description' => "Vous pouvez configurer ici des paramètres additionnels lorsque vous utilisez sendmail, par exemple -f %s (pour mieux éviter que les mails soient marqués comme spam)",
	
	// notification body
	'html_email_handler:notification:footer:settings' => "Configurez vos notifications %sen cliquant sur ce lien%s",
);
