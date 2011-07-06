<?php 

	$plugin = $vars["entity"];
	
	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);

?>
<h3 class="settings"><?php echo elgg_echo("html_email_handler:settings:notifications:title"); ?></h3>
<div><?php echo elgg_echo("html_email_handler:settings:notifications:description"); ?></div>
<br />

<div>
	<?php 
	echo elgg_echo("html_email_handler:settings:notifications");
	echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[notifications]", "options_values" => $noyes_options, "value" => $plugin->notifications)); 
	?>
</div>