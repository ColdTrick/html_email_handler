<?php

namespace ColdTrick\HTMLEmailHandler;

use ColdTrick\HTMLEmailHandler\Parts\HtmlPart;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Header\ContentType;
use Elgg\Email\Attachment;

class Email {
	
	/**
	 * Limit the subject length of the e-mail
	 *
	 * Outlook has been known to have problems with long subjects
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'system:email'
	 *
	 * @return void|\Elgg\Email
	 */
	public static function limitSubjectLength(\Elgg\Hook $hook) {
		
		if (elgg_get_plugin_setting('limit_subject', 'html_email_handler') !== 'yes') {
			return;
		}
		
		$email = $hook->getValue();
		if (!$email instanceof \Elgg\Email) {
			return;
		}
		
		$email->setSubject(elgg_get_excerpt($email->getSubject(), 175));
		
		return $email;
	}
	
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
		} elseif (is_string($html_text)) {
			// html text already provided
			if (elgg_extract('convert_css', $params, true)) {
				// still needs to be converted to inline CSS
				$html_text = html_email_handler_css_inliner($html_text);
			}
		} else {
			$recipient = self::findRecipient($email);
			$html_text = html_email_handler_make_html_body([
				'subject' => $email->getSubject(),
				'body' => $email->getBody(),
				'recipient' => $recipient,
			]);
		}
		
		// normalize urls in text
		$html_text = html_email_handler_normalize_urls($html_text);
		// base64 embed images (when enabled)
		$html_text = html_email_handler_base64_encode_images($html_text);
		// attach images (when enabled)
		$image_attachments = html_email_handler_attach_images($html_text);
		
		if (is_string($image_attachments)) {
			// no need to split html message and images
			return new HtmlPart($html_text);
		}
		
		// split html body and relate images
		$message = new MimeMessage();
		
		$html_part = new HtmlPart($image_attachments['text']);
		$message->addPart($html_part);
		
		foreach ($image_attachments['images'] as $image_data) {
			$attachment = Attachment::factory([
				'id' => $image_data['uid'],
				'content' => $image_data['data'],
				'type' => $image_data['content-type'],
				'filename' => $image_data['name'],
				'encoding' => Mime::ENCODING_BASE64,
				'disposition' => Mime::DISPOSITION_INLINE,
				'charset' => 'UTF-8',
			]);
			
			$message->addPart($attachment);
		}
		
		$part = new MimePart($message->generateMessage());
		$part->setType(Mime::MULTIPART_RELATED);
		$part->setBoundary($message->getMime()->boundary());
		
		return $part;
	}
	
	/**
	 * Find the user who will receive the e-mail
	 *
	 * @param \Elgg\Email $email the e-mail
	 *
	 * @return void|\ElggUser
	 */
	protected static function findRecipient(\Elgg\Email $email) {
		
		$to = $email->getTo();
		if (empty($to)) {
			return;
		}
		
		$users = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'metadata_name_value_pairs' => [
				[
					'name' => 'name',
					'value' => $to->getName(),
				],
				[
					'name' => 'email',
					'value' => $to->getEmail(),
				],
			],
		]);
		return elgg_extract(0, $users);
	}
}
