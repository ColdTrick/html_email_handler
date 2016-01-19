<?php

namespace ColdTrick\HTMLEmailHandler;

class Email {
	
	/**
	 * Sends out a full HTML mail
	 *
	 * @param string $hook         'email'
	 * @param string $type         'system'
	 * @param array  $return_value In the format:
	 *     to => STR|ARR of recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 *     from => STR of senden in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 *     subject => STR with the subject of the message
	 *     body => STR with the message body
	 *     plaintext_message STR with the plaintext version of the message
	 *     html_message => STR with the HTML version of the message
	 *     cc => NULL|STR|ARR of CC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 *     bcc => NULL|STR|ARR of BCC recipients in RFC-2822 format (http://www.faqs.org/rfcs/rfc2822.html)
	 *     date => NULL|UNIX timestamp with the date the message was created
	 *     attachments => NULL|ARR of array(array('mimetype', 'filename', 'content'))
	 * @param array  $params       The unmodified core parameters
	 *
	 * @return void|bool
	 */
	public static function email($hook, $type, $return_value, $params) {
		static $plugin_setting;
		
		if (!isset($plugin_setting)) {
			$plugin_setting = false;
		
			// do we need to handle sending of mails?
			if (elgg_get_plugin_setting('notifications', 'html_email_handler') === 'yes') {
				$plugin_setting = true;
			}
		}
		
		if (!$plugin_setting) {
			return ;
		}
		
		// if someone else handled sending they should return true|false
		if (empty($return_value) || !is_array($return_value)) {
			return;
		}
		
		$additional_params = elgg_extract('params', $return_value);
		if (is_array($additional_params)) {
			$return_value = array_merge($return_value, $additional_params);
		}
		
		return html_email_handler_send_email($return_value);
	}
}
