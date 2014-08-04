<?php
/**
 * Intercept saving function
 * @uses array $_REQUEST['params']    A set of key/value pairs to save to the ElggPlugin entity
 * @uses int   $_REQUEST['plugin_id'] The ID of the plugin
 */
$params = get_input('params');
$plugin_id = get_input('plugin_id');
$plugin = elgg_get_plugin_from_id($plugin_id);
if (!($plugin instanceof ElggPlugin)) {
	register_error(elgg_echo('plugins:settings:save:fail', array($plugin_id)));
	forward(REFERER);
};
$result = false;
foreach ($params as $k => $v) {
    switch ($k) {
        case 'smtp_pass':
            $result = $plugin->setSetting($k, setuppassword($v));
            break;
        default:
            $result = $plugin->setSetting($k, $v); //where no changes are required
            break;
    }
		
		if (!$result) {
			register_error(elgg_echo('plugins:settings:save:fail', array($plugin_name)));
			forward(REFERER);
			exit;
		}
	};
system_message(elgg_echo('plugins:settings:save:ok', array($plugin_name)));
forward(REFERER);

/**
 * Takes care of encrypting the password if its not blank
 * @param string $pstring The password string to be encoded
 * @return string   The encrypted string
 */
function setuppassword($pstring){
    if(trim($pstring) != '' || $pstring != 'xxxxxxxx') return base64_encode($pstring);
}
?>