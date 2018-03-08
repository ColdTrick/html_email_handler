<?php

$subject = elgg_extract("subject", $vars);
$message = elgg_extract("body", $vars);

$language = elgg_extract("language", $vars, get_current_language());
$recipient = elgg_extract("recipient", $vars);

$head = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
$head .= '<base target="_blank" />';

if (!empty($subject)) {
	$head .= elgg_format_element('title', [], $subject);
}

$css = elgg_view('css/html_email_handler/notification');

$site_link = elgg_view('output/url', [
	'href' => elgg_get_site_url(),
	'text' => elgg_get_site_entity()->getDisplayName(),
	'is_trusted' => true,
]);

$body_title = !empty($subject) ? elgg_view_title($subject) : '';

$notification_footer = '';
if (!empty($recipient) && ($recipient instanceof ElggUser)) {
	$settings_url = elgg_normalize_url("settings/user/{$recipient->username}");
	if (elgg_is_active_plugin('notifications')) {
		$settings_url = elgg_normalize_url("notifications/personal/{$recipient->username}");
	}
	$notification_footer = elgg_echo('html_email_handler:notification:footer:settings', [
		"<a href='{$settings_url}'>",
		'</a>',
	]);
}

$body = <<<__BODY
<style type="text/css">{$css}</style>

<div id="notification_container">
	<div id="notification_header">{$site_link}</div>
	<div id="notification_wrapper">
		{$body_title}
	
		<div id="notification_content">
			{$message}
		</div>
	</div>
	
	<div id="notification_footer">
		{$notification_footer}
		<div class="clearfloat"></div>
	</div>
</div>
__BODY;

echo elgg_view('page/elements/html', [
	'head' => $head,
	'body' => $body,
]);
