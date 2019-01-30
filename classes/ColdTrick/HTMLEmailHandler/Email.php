<?php

namespace ColdTrick\HTMLEmailHandler;

use ColdTrick\HTMLEmailHandler\Parts\HtmlPart;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Header\ContentType;

class Email {
	
	/**
	 * Add an html part to the email message
	 *
	 * @param \Elgg\Hook $hook 'zend:message', 'system:email'
	 *
	 * @return void|\Zend\Mail\Message
	 */
	public static function makeHtmlMail(\Elgg\Hook $hook) {
		
		/* @var $email \Elgg\Email */
		$email = $hook->getParam('email');
		
		// multipart
		$multipart = new MimeMessage();
		
		$multipart->addPart(self::makePlainTextPart($email));
		$multipart->addPart(self::makeHtmlPart($email));
		
		// support attachments
		$attachments = $email->getAttachments();
		if (!empty($attachments)) {
			// mail content
			$multipart_content = new MimePart($multipart->generateMessage());
			$multipart_content->setType(Mime::MULTIPART_ALTERNATIVE);
			$multipart_content->setBoundary($multipart->getMime()->boundary());
			
			// combined
			$new_body = new MimeMessage();
			$new_body->addPart($multipart_content);
			
			foreach ($attachments as $a) {
				$new_body->addPart($a);
			}
			
			$message_content_type = Mime::MULTIPART_RELATED;
		} else {
			$new_body = $multipart;
			$message_content_type = Mime::MULTIPART_ALTERNATIVE;
		}
		
		/* @var $message \Zend\Mail\Message */
		$message = $hook->getValue();
		
		$message->getHeaders()->removeHeader('content-type');
		$message->setBody($new_body);
		
		$headers = $message->getHeaders();
		foreach ($headers as $header) {
			if (!$header instanceof ContentType) {
				continue;
			}
			
			$header->setType($message_content_type);
			$header->addParameter('boundary', $new_body->getMime()->boundary());
			break;
		}
		
		return $message;
	}
	
	/**
	 * Make the plain text part for e-mail message
	 *
	 * @param \Elgg\Email $email the e-mail to get information from
	 *
	 * @return \Zend\Mime\Part
	 */
	protected static function makePlainTextPart(\Elgg\Email $email) {
		
		$plain_text = elgg_strip_tags($email->getBody());
		$plain_text = html_entity_decode($plain_text, ENT_QUOTES, 'UTF-8');
		$plain_text = wordwrap($plain_text);
		
		$plain_text_part = new MimePart($plain_text);
		$plain_text_part->setId('plaintext');
		$plain_text_part->setType(Mime::TYPE_TEXT);
		$plain_text_part->setCharset('UTF-8');
		
		return $plain_text_part;
	}
	
	/**
	 * Make the html part of the e-mail message
	 *
	 * @param \Elgg\Email $email the e-mail to get information from
	 *
	 * @return \Zend\Mime\Part
	 */
	protected static function makeHtmlPart(\Elgg\Email $email) {
		
		$mail_params = $email->getParams();
		$html_text = elgg_extract('html_message', $mail_params);
		if ($html_text instanceof MimePart) {
			return $html_text;
		} elseif (!empty($html_text)) {
			// html text already provided
			if (elgg_extract('convert_css', $params, true)) {
				// still needs to be converted to inline CSS
				$html_text = html_email_handler_css_inliner($html_text);
			}
		} else {
			$html_text = html_email_handler_make_html_body([
				'subject' => $email->getSubject(),
				'body' => $email->getBody(),
			]);
		}
		
		return new HtmlPart($html_text);
	}
}
