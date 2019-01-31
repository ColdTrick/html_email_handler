<?php

namespace ColdTrick\HTMLEmailHandler;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\TransferException;

class ImageFetcher {
	
	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client;
	
	/**
	 * Create new image fetcher
	 */
	public function __construct() {
		
		$options = [
			RequestOptions::TIMEOUT => 5,
			RequestOptions::HTTP_ERRORS => false,
		];
		
		if (!(bool) elgg_get_plugin_setting('proxy_disable_ssl_verify', 'html_email_handler')) {
			$options[RequestOptions::VERIFY] = false;
		}
		
		$proxy_host = elgg_get_plugin_setting('proxy_host', 'html_email_handler');
		if (!empty($proxy_host)) {
			$proxy_port = (int) elgg_get_plugin_setting('proxy_port', 'html_email_handler');
			if (!$proxy_port > 0) {
				$proxy_host = rtrim($proxy_host, ':') . ":{$proxy_port}";
			}
			
			$options[RequestOptions::PROXY] = $proxy_host;
		}
		
		$this->client = new Client($options);
	}
	
	/**
	 * Get an image
	 *
	 * @param string $image_url the image url to get
	 *
	 * @return false|array result contains
	 * 	- data: the image data
	 * 	- content-type: the content type of the image
	 * 	- name: the name of the image
	 */
	public function getImage($image_url) {
		if (empty($image_url)) {
			return false;
		}
		
		$options = [];
		
		$image_url = htmlspecialchars_decode($image_url);
		$image_url = elgg_normalize_url($image_url);
		
		$cache = $this->loadFromCache($image_url);
		if (!empty($cache)) {
			return $cache;
		}
		
		$site = elgg_get_site_entity();
		if (stripos($image_url, $site->getURL()) === 0) {
			// internal url, can use session cookie
			$cookie_config = elgg()->config->getCookieConfig();
			$session = elgg_get_session();
			
			$cookies = [
				$cookie_config['session']['name'] => $session->getId(),
			];
			
			$domain = $cookie_config['session']['domain'] ?: $site->getDomain();
			
			$cookiejar = CookieJar::fromArray($cookies, $domain);
			$options[RequestOptions::COOKIES] = $cookiejar;
		}
		
		try {
			$response = $this->client->get($image_url, $options);
		} catch (TransferException $e) {
			// this shouldn't happen, but just in case
			return false;
		}
		
		if ($response->getStatusCode() !== ELGG_HTTP_OK) {
			return false;
		}
		
		$result = [
			'data' => $response->getBody()->getContents(),
			'content-type' => $response->getHeaderLine('content-type'),
			'name' => basename($image_url),
		];
		
		$s = $this->saveToCache($image_url, $result);
		
		return $result;
	}
	
	/**
	 * Get an image as base64 encoded replacement
	 *
	 * @param string $image_url the image url to fetch
	 *
	 * @return false|string
	 */
	public function getImageBase64($image_url) {
		
		$result = $this->getImage($image_url);
		if (empty($result)) {
			return false;
		}
		
		// build a valid uri
		// https://en.wikipedia.org/wiki/Data_URI_scheme
		return $result['content-type'] . ';charset=UTF-8;base64,' . base64_encode($result['data']);
	}
	
	/**
	 * Load an image url from cache
	 *
	 * @param string $image_url the url to load
	 *
	 * @return void|array
	 */
	protected function loadFromCache($image_url) {
		
		if (empty($image_url)) {
			return;
		}
		
		$checksum = md5($image_url);
		
		return elgg_load_system_cache("html_email_handler_{$checksum}");
	}
	
	/**
	 * Save image data in system cache for easy reuse
	 *
	 * @param string $image_url the image url
	 * @param array  $data      the image data
	 *
	 * @return bool
	 */
	protected function saveToCache(string $image_url, array $data) {
		
		if (empty($data) || empty($image_url)) {
			return false;
		}
		
		$checksum = md5($image_url);
		
		return elgg_save_system_cache("html_email_handler_{$checksum}", $data);
	}
}
