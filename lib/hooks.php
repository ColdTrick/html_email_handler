<?php

	/**
	 * Hook to handle emails send by elgg_send_email
	 * 
	 * @param string $hook
	 * @param string $type
	 * @param bool $return
	 * @param array $params
	 * 		to 		=> who to send the email to
	 * 		from 	=> who is the sender
	 * 		subject => subject of the message
	 * 		body 	=> message
	 * 		params 	=> optional params
	 */
	/* Note : attachments can be passed through a $params['attachments'] :
	 * Warning : passing a file through filepath is not functionnal yet
	 * 	$attachments[] = array(
	 * 		'content' => $file_content,
	 * 		'filepath' => $file_content, // Alternate file path for file content retrieval
	 * 		'filename' => $file_content,
	 * 		'mimetype' => $file_content,
	 * 	);
	*/
	function html_email_handler_email_hook($hook, $type, $return, $params){
		// generate HTML mail body
		$html_message = html_email_handler_make_html_body($params["subject"], $params["body"]);
		
		// set options for sending
		$options = array(
			"to" => $params["to"],
			"from" => $params["from"],
			"subject" => $params["subject"],
			"html_message" => $html_message,
			"plaintext_message" => $params["body"]
		);
		// Add optional attachments
		if ($params['attachments']) { $options['attachments'] = $params["attachments"]; }
		
		return html_email_handler_send_email($options);
	}
	/**
	 * Automatically triggered notification on 'create' events that looks at registered
	 * objects and attempts to send notifications to anybody who's interested
	 *
	 * @see register_notification_object
	 *
	 * @param string $event       create
	 * @param string $object_type mixed
	 * @param mixed  $object      The object created
	 *
	 * @return bool
	 * @access private
	 */
	/* Note : this hook is used to add a new hook that let's plugins set $params 
	 * This makes it easy for plugins to add attachments
	 * (Note : this is more generic to support further settings (just in case...)
	 * Use : return $options['attachments'] = $attachments
	 * With $attachments being an array of file attachments :
	 * $attachments[] = array(
	 * 		'content' => $file_content, // File content
	 * 		'filepath' => $file_content, // Alternate file path for file content retrieval
	 * 		'filename' => $file_content, // Attachment file name
	 * 		'mimetype' => $file_content, // MIME type of attachment
	 * 	);
	 */
	function html_email_handler_object_notifications_hook($hook, $entity_type, $returnvalue, $params) {
		// Get config data
		global $CONFIG, $SESSION, $NOTIFICATION_HANDLERS;

		// Facyla : warning, if a plugin hook returned "true" (e.g. for blocking notification process), 
		// this wouldn't be handled, so we should check it before going through the whole process !!
		if ($returnvalue === true) return true;

		$event = $params['event'];
		$object = $params['object'];
		$object_type = $params['object_type'];

		// Have we registered notifications for this type of entity?
		$object_type = $object->getType();
		if (empty($object_type)) {
			$object_type = '__BLANK__';
		}

		$object_subtype = $object->getSubtype();
		if (empty($object_subtype)) {
			$object_subtype = '__BLANK__';
		}

		if (isset($CONFIG->register_objects[$object_type][$object_subtype])) {
			$subject = $CONFIG->register_objects[$object_type][$object_subtype];
			$string = $subject . ": " . $object->getURL();

			// Get users interested in content from this person and notify them
			// (Person defined by container_guid so we can also subscribe to groups if we want)
			foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
				$interested_users = elgg_get_entities_from_relationship(array(
					'site_guids' => ELGG_ENTITIES_ANY_VALUE,
					'relationship' => 'notify' . $method,
					'relationship_guid' => $object->container_guid,
					'inverse_relationship' => TRUE,
					'type' => 'user',
					'limit' => false
				));
				/* @var ElggUser[] $interested_users */

				if ($interested_users && is_array($interested_users)) {
					foreach ($interested_users as $user) {
						if ($user instanceof ElggUser && !$user->isBanned()) {
							if (($user->guid != $SESSION['user']->guid) && has_access_to_entity($object, $user)
							&& $object->access_id != ACCESS_PRIVATE) {
								$body = elgg_trigger_plugin_hook('notify:entity:message', $object->getType(), array(
									'entity' => $object,
									'to_entity' => $user,
									'method' => $method), $string);
								if (empty($body) && $body !== false) {
									$body = $string;
								}
								
								// this is new, trigger a hook to make a custom subject
								$new_subject = elgg_trigger_plugin_hook("notify:entity:subject", $object->getType(), array(
									"entity" => $object,
									"to_entity" => $user,
									"method" => $method), $subject);
								// Keep new value only if correct subject
								if (!empty($new_subject)) { $subject = $new_subject; }
								
								// Params hook : see doc above
								$options = elgg_trigger_plugin_hook('notify:entity:params', $object->getType(), array(
									'entity' => $object,
									'to_entity' => $user,
									'method' => $method), null);
								
								if ($body !== false) {
									notify_user($user->guid, $object->container_guid, $subject, $body,
										$options, array($method));
								}
							}
						}
					}
				}
			}
		}
		// Stop notifications here once done
		return true;
	}
