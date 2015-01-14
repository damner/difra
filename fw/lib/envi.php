<?php

namespace Difra;

/**
 * Class Envi
 *
 * @package Difra
 */
class Envi {

	/** @var string Environment mode (web, cli, include) */
	protected static $mode = 'include';
	/** @var string|null Custom URI (useful for unit testing) */
	private static $customUri = null;
	/** @var string|null Current URI */
	private static $requestedUri = null;
	/** @var string|null Current URI without urldecode() */
	private static $requestedUriRaw = null;

	/**
	 * Get environment mode
	 */
	public static function getMode() {

		return self::$mode;
	}

	/**
	 * Set environment mode
	 *
	 * @param $mode
	 */
	public static function setMode($mode) {

		self::$mode = $mode;
	}

	/**
	 * Get current URI
	 *
	 * @param bool $raw Don't urldecode() URI
	 * @return string
	 */
	public static function getUri($raw = false) {

		if(is_null(self::$requestedUri)) {
			if(!is_null(self::$customUri)) {
				self::$requestedUri = self::$customUri;
			} elseif(!empty($_SERVER['URI'])) { // это для редиректов запросов из nginx
				self::$requestedUri = $_SERVER['URI'];
			} elseif(!empty($_SERVER['REQUEST_URI'])) {
				self::$requestedUri = $_SERVER['REQUEST_URI'];
			} else {
				return null;
			}
			if(false !== strpos(self::$requestedUri, '?')) {
				self::$requestedUri = substr(self::$requestedUri, 0, strpos(self::$requestedUri, '?'));
			}
			self::$requestedUriRaw = '/' . trim(self::$requestedUri, '/');

			self::$requestedUri = urldecode(self::$requestedUriRaw);
		}
		return $raw ? self::$requestedUriRaw : self::$requestedUri;
	}

	/**
	 * Set current URI

	 *
*@param string $uri
	 */
	public static function setUri($uri) {

		self::$customUri = $uri;
		self::$requestedUri = null;
		self::$requestedUriRaw = null;
	}

	/**
	 * Get current environment state as XML node attributes

	 *
*@param \DOMElement|\DOMNode $node
	 */
	public static function getStateXML($node) {

		$config = self::getState();
		foreach($config as $k => $v) {
			$node->setAttribute($k, $v);
		}
	}

	/**
	 * Get current environment state as array
	 */
	public static function getState() {

		return [
			'locale'   => Envi\Setup::getLocale(),
			'host'     => self::getSubsite(),
			'hostname' => self::getHost(),
			'mainhost' => self::getHost(true)
		];
	}

	/**
	 * Detects subsite:
	 * 1. By server variable VHOST_NAME.
	 * 2. By Host names sub.subdomain.domain.com www.sub.subdomain.domain.com subdomain.domain.com
	 *    www.subdomain.domain.com domain.com www.domain.com.
	 * 3. "default" subsite name.

	 *
*@return string|bool
	 */
	public static function getSubsite() {

		static $site = null;
		if(!is_null($site)) {
			return $site;
		}

		// default behavior: site is defined by web server
		if(!empty($_SERVER['VHOST_NAME'])) {
			return $site = $_SERVER['VHOST_NAME'];
		}

		// no host name is available (most likely environment is not web server)
		if(!$host = self::getHost()) {
			return $site = 'default';
		}

		// automatic behavior: try to compare host name to existing directories in sites folder
		$sitesLocation = DIR_ROOT . 'sites/';
		while($host) {
			if(is_dir($sitesLocation . $host)) {
				return $site = $host;
			}
			if(is_dir($sitesLocation . 'www.' . $host)) {
				return $site = 'www.' . $host;
			}
			$host = explode('.', $host, 2);
			$host = !empty($host[1]) ? $host[1] : false;
		}
		return $site = 'default';
	}

	/**
	 * Get host name from request
	 *
	 * @param bool $main Get "main" host name (for subdomains)
	 * @return string
	 */
	public static function getHost($main = false) {

		if($main and !empty($_SERVER['VHOST_MAIN'])) {
			return $_SERVER['VHOST_MAIN'];
		}
		if(!empty($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		}
		return gethostname();
	}
}