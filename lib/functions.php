<?php
/**
 * All helpder functions for this plugin can be found here
 */

use ColdTrick\HTMLEmailHandler\ImageFetcher;

/**
 * This function converts CSS to inline style, the CSS needs to be found in a <style> element
 *
 * @param string $html_text the html text to be converted
 *
 * @return false|string
 */
function html_email_handler_css_inliner($html_text) {
	$result = false;
	
	if (!empty($html_text) && defined("XML_DOCUMENT_NODE")) {
		$css = "";
		
		// set custom error handling
		libxml_use_internal_errors(true);
		
		$dom = new DOMDocument();
		$dom->loadHTML($html_text);
		
		$styles = $dom->getElementsByTagName("style");
		
		if (!empty($styles)) {
			$style_count = $styles->length;
			
			for ($i = 0; $i < $style_count; $i++) {
				$css .= $styles->item($i)->nodeValue;
			}
		}
		
		// clear error log
		libxml_clear_errors();
		
		$emo = new Pelago\Emogrifier($html_text, $css);
		$result = $emo->emogrify();
	}
	
	return $result;
}

/**
 * Make the HTML body from a $options array
 *
 * @param array $options the options, supports:
 * 	- subject: the subject of the e-mail
 * 	- body: the body of the e-mail
 * 	- language: the language of the e-mail (default: current language)
 *
 * @return string
 */
function html_email_handler_make_html_body(array $options = []) {
	$defaults = [
		'subject' => '',
		'body' => '',
		'language' => get_current_language()
	];
	
	$options = array_merge($defaults, $options);
	
	$options['body'] = elgg()->html_formatter->formatBlock($options['body']);

	// generate HTML mail body
	$result = elgg_view('html_email_handler/notification/body', $options);
	
	if (defined('XML_DOCUMENT_NODE')) {
		if ($transform = html_email_handler_css_inliner($result)) {
			$result = $transform;
		}
	}
	
	return $result;
}

/**
 * Normalize all URL's in the text to full URL's
 *
 * @param string $text the text to check for URL's
 *
 * @return string
 */
function html_email_handler_normalize_urls($text) {
	static $pattern = '/\s(?:href|src)=([\'"]\S+[\'"])/i';
	
	if (empty($text)) {
		return $text;
	}
	
	// find all matches
	$matches = [];
	preg_match_all($pattern, $text, $matches);
	
	if (empty($matches) || !isset($matches[1])) {
		return $text;
	}
	
	// go through all the matches
	$urls = $matches[1];
	$urls = array_unique($urls);
	
	foreach ($urls as $url) {
		// remove wrapping quotes from the url
		$real_url = substr($url, 1, -1);
		// normalize url
		$new_url = elgg_normalize_url($real_url);
		// make the correct replacement string
		$replacement = str_replace($real_url, $new_url, $url);
		
		// replace the url in the content
		$text = str_replace($url, $replacement, $text);
	}
	
	return $text;
}

/**
 * Convert images to inline images
 *
 * This can be enabled with a plugin setting (default: off)
 *
 * @param string $text the text of the message to embed the images from
 *
 * @return string
 */
function html_email_handler_base64_encode_images($text) {
	
	if (empty($text) || elgg_get_plugin_setting('embed_images', 'html_email_handler') !== 'base64') {
		return $text;
	}
	
	$image_urls = html_email_handler_find_images($text);
	if (empty($image_urls)) {
		return $text;
	}
	
	$fetcher = new ImageFetcher();
	
	foreach ($image_urls as $url) {
		// remove wrapping quotes from the url
		$image_url = substr($url, 1, -1);
		
		// get the image contents
		$contents = $fetcher->getImageBase64($image_url);
		if (empty($contents)) {
			continue;
		}
		
		// build inline image
		$replacement = str_replace($image_url, "data:{$contents}", $url);
		
		// replace in text
		$text = str_replace($url, $replacement, $text);
	}
	
	return $text;
}

/**
 * Find img src's in text
 *
 * @param string $text the text to search though
 *
 * @return false|array
 */
function html_email_handler_find_images($text) {
	static $pattern = '/\ssrc=([\'"]\S+[\'"])/i';
	
	if (empty($text)) {
		return false;
	}
	
	// find all matches
	$matches = [];
	preg_match_all($pattern, $text, $matches);
	
	if (empty($matches) || !isset($matches[1])) {
		return false;
	}
	
	// return all the found image urls
	return array_unique($matches[1]);
}

/**
 * Get information needed for attaching the images to the e-mail
 *
 * @param string $text the html text to search images in
 *
 * @return string|array
 */
function html_email_handler_attach_images($text) {
	
	if (empty($text) || elgg_get_plugin_setting('embed_images', 'html_email_handler') !== 'attach') {
		return $text;
	}
	
	// get images
	$image_urls = html_email_handler_find_images($text);
	if (empty($image_urls)) {
		return $text;
	}
	
	$fetcher = new ImageFetcher();
	
	$result = [
		'images' => [],
	];
	
	foreach ($image_urls as $url) {
		// remove wrapping quotes from the url
		$image_url = substr($url, 1, -1);
		
		// get the image data
		$image_data = $fetcher->getImage($image_url);
		if (empty($image_data)) {
			continue;
		}
		
		// Unique ID
		$uid = uniqid();
		
		$image_data['uid'] = $uid;
		
		$result['images'][] = $image_data;
		
		// replace url in the text with uid
		$replacement = str_replace($image_url, "cid:{$uid}", $url);
		
		$text = str_replace($url, $replacement, $text);
	}
	
	// return new text
	$result['text'] = $text;
	
	// return result
	return $result;
}
