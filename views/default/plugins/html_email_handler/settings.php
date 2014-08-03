<?php

$plugin = elgg_extract("entity", $vars);

$noyes_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

$conn_options = array(
        "na" => "None",
	"ssl" => "SSL",
	"tls" => "TLS"
);

$auth_options = array(
        "LOGIN" => "Login(default)",
	"PLAIN" => "Plaintext",
	"CRAM-MD5" => "CRAM-MD5 Digest"
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

echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_user");
echo elgg_view("input/text", array("name" => "params[smtp_user]", "value" => $plugin->smtp_user));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_user:description") . "</div>";
echo "</div>";

$em_password =(trim($plugin->smtp_pass) !='') ? 'xxxxxxxx' : ''; /*returns place holder if password is set or leaves it blank for blank passwords */
echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_pass");
echo elgg_view("input/text", array("name" => "params[smtp_pass]", "value" => $em_password));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_pass:description") . "</div>";
echo "</div>";

echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_contype");
echo elgg_view("input/dropdown", array("name" => "params[smtp_contype]", "options_values" =>$conn_options, $plugin->smtp_contype, "class" => "mls"));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_contype:description") . "</div>";
echo "</div>";

echo "<div>";
echo elgg_echo("html_email_handler:settings:smtp_authtype");
echo elgg_view("input/dropdown", array("name" => "params[smtp_authtype]", "options_values" =>$auth_options, $plugin->smtp_authtype, "class" => "mls"));
echo "<div class='elgg-subtext'>" . elgg_echo("html_email_handler:settings:smtp_authtype:description") . "</div>";
echo "</div>";