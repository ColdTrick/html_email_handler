<?php 

	$plugin = $vars["entity"];
	
	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);

echo elgg_echo("html_email_handler:settings:notifications:description");
echo "<br />";
echo elgg_echo("html_email_handler:settings:notifications");
echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[notifications]", "options_values" => $noyes_options, "value" => $plugin->notifications)); 
echo "<br /><br />";