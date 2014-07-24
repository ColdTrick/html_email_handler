<?php

$plugin = elgg_extract("entity", $vars);

$noyes_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

$site_email = elgg_get_site_entity()->email;

// present settings
echo "<div>";
echo elgg_echo("html_email_handler:settings:notifications:description");
echo "</div>";

echo "<div>";
echo elgg_echo("html_email_handler:settings:notifications");
echo elgg_view("input/dropdown", array("name" => "params[notifications]", "options_values" => $noyes_options, "value" => $plugin->notifications, "class" => "mls"));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:notifications:subtext") . "</div>";
echo "</div>";

echo "<div>";
echo elgg_echo("html_email_handler:settings:sendmail_options");
echo elgg_view("input/text", array("name" => "params[sendmail_options]", "value" => $plugin->sendmail_options));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:sendmail_options:description", array($site_email)) . "</div>";
echo "</div>";

echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_server");
echo elgg_view("input/text", array("name" => "params[smtp_server]", "value" => $plugin->smtp_server));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_server:description") . "</div>";
echo "</div>";


echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_port");
echo elgg_view("input/text", array("name" => "params[smtp_port]", "value" => $plugin->smtp_port));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_port:description") . "</div>";
echo "</div>";



