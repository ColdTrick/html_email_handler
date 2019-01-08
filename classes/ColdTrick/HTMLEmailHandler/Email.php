<?php

namespace ColdTrick\HTMLEmailHandler;

use ColdTrick\HTMLEmailHandler\Parts\HtmlPart;
use Zend\Mime\Mime;
use Zend\Mime\Part;
use Zend\Mime\Message;
use Zend\Mail\Header\ContentType;

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
	 * @deprecated
	 * @return void|bool
	 */
	public static function emailHandler($hook, $type, $return_value, $params) {
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
	
	/**
	 * Add an html part to the email message
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'system:email'
	 *
	 * @return void|\Elgg\Email
	 */
	public static function addHtmlPart(\Elgg\Hook $hook) {
		
		if (!elgg_get_plugin_setting('html_part', 'html_email_handler')) {
			return;
		}
		
		/* @var $email \Elgg\Email */
		$email = $hook->getValue();
		
		$html_body = html_email_handler_make_html_body([
			'subject' => $email->getSubject(),
			'body' => $email->getBody(),
		]);
		$html_body = PHP_EOL . $html_body . PHP_EOL;
		
		$part = new HtmlPart($html_body);
		
		$email->addAttachment($part);
		
		return $email;
	}
	
	/**
	 * Add an html part to the email message
	 *
	 * @param \Elgg\Hook $hook 'zend:message', 'system:email'
	 *
	 * @return void|\Zend\Mail\Message
	 */
	public static function correctParts(\Elgg\Hook $hook) {
		
		/* @var $message \Zend\Mail\Message */
		$message = $hook->getValue();
		
		$plain = [];
		$html = [];
		$attachments = [];
		
		$body = $message->getBody();
		
		/* @var $part \Zend\Mime\Part */
		foreach ($body->getParts() as $part) {
			
			switch ($part->getType()) {
				case Mime::TYPE_TEXT:
					$plain[] = $part;
					break;
				case Mime::TYPE_HTML:
					$html[] = $part;
					break;
				default:
					$attachments[] = $part;
					break;
			}
		}
		
		if (empty($plain) || empty($html)) {
			return;
		}
		
		// multipart
		$multipart = new Message();
		
		foreach ($plain as $p) {
			$multipart->addPart($p);
		}
		
		foreach ($html as $h) {
			$multipart->addPart($h);
		}
		
		$multipart_content = new Part($multipart->generateMessage());
		$multipart_content->setType(Mime::MULTIPART_ALTERNATIVE);
		$multipart_content->setBoundary($multipart->getMime()->boundary());
		
		$new_body = new Message();
		$new_body->addPart($multipart_content);
				
		foreach ($attachments as $a) {
			$new_body->addPart($a);
		}
		
		$message->setBody($new_body);
		$message->getHeaders()->removeHeader('content-type');
		$message->getHeaders()->addHeaderLine('content-type', Mime::MULTIPART_ALTERNATIVE . '; boundary="' . $multipart->getMime()->boundary() . '"');
		
		return $message;
	}
}
