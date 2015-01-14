<?php

namespace Difra;

/**
 * Project configuration
 * Class Config

 *
*@package Difra
 */
class Config {

	/** @var array Current configuration */
	private $config = null;
	/** @var bool Modified flag */
	private $modified = false;

	/** @var array Default configuration */
	private $defaultConfig = array(
		'instances' => array(
			'main' => array(
				'withAll' => true
			),
			'adm' => array(
				'withAll' => true
			)
		)
	);

	/**
	 * Singleton

	 *
*@static
	 * @return Config
	 */
	static public function getInstance() {

		static $instance = null;
		return $instance ? $instance : $instance = new self;
	}

	/**
	 * Destructor
	 */
	public function __destruct() {

		$this->save();
	}

	/**
	 * Save configuration
	 *
	 * @return bool
	 */
	public function save() {

		if(!$this->modified) {
			return true;
		}
		$diff = $this->diff();
		try {
			$db = MySQL::getInstance();
			$db->query('DELETE FROM `config`');
			$db->query("INSERT INTO `config` SET `config`='" . $db->escape(serialize($diff)) . "'");
			Cache::getInstance()->remove('config');
		} catch(Exception $e) {
			$e->notify();
			return false;
		}
		$this->modified = false;
		return true;
	}

	/**
	 * Finds difference between saved configuration and current one

	 *
*@return array
	 */
	private function diff() {

		return $this->subDiff($this->loadFileConfigs(), $this->config);
	}

	/**
	 * Recursive part for diff()

	 *
*@param array $a1
	 * @param array $a2
	 * @return array
	 */
	private function subDiff($a1, $a2) {

		if(empty($a2)) {
			return array();
		}
		$diff = array();
		foreach($a2 as $k => $v) {
			if(!isset($a1[$k])) {
				$diff[$k] = $v;
			} elseif(is_array($v)) {
				$d = $this->subDiff($a1[$k], $a2[$k]);
				if(!empty($d)) {
					$diff[$k] = $a2[$k];
				}
			} elseif($a1[$k] !== $v) {
				$diff[$k] = $v;
			}
		}
		foreach($a1 as $k => $v) {
			if(!isset($a2[$k])) {
				$diff[$k] = null;
			}
		}
		return $diff;
	}

	/**
	 * Get configuration from config.php
	 *
	 * @return array
	 */
	private function loadFileConfigs() {

		static $newConfig = null;
		if(is_null($newConfig)) {
			$newConfig = $this->defaultConfig;
			if(is_file(DIR_ROOT . '/config.php')) {
				/** @noinspection PhpIncludeInspection */
				$conf2 = include(DIR_ROOT . 'config.php');
				$newConfig = $this->merge($newConfig, $conf2);
			}
			if(is_file(DIR_SITE . '/config.php')) {
				/** @noinspection PhpIncludeInspection */
				$conf2 = include(DIR_SITE . 'config.php');
				$newConfig = $this->merge($newConfig, $conf2);
			}
		}
		return $newConfig;
	}

	/**
	 * Merge two configuration array
	 *
	 * @param array $a1
	 * @param array $a2
	 * @return mixed
	 */
	function merge($a1, $a2) {

		foreach($a2 as $k => $v) {
			$a1[$k] = $v;
		}
		return $a1;
	}

	/**
	 * Get configuration item value
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {

		$this->load();
		return isset($this->config[$key]) ? $this->config[$key] : null;
	}

	/**
	 * Load configuration
	 */
	private function load() {

		if(!is_null($this->config)) {
			return;
		}
		$cache = Cache::getInstance();
		if($c = $cache->get('config')) {
			$this->config = $c;
			return;
		}
		$this->config = $this->loadFileConfigs();
		try {
			$db = MySQL::getInstance();
			$conf = $db->fetchOne('SELECT `config` FROM `config` LIMIT 1');
			$dbconf = @unserialize($conf);
			if(is_array($dbconf)) {
				$this->config = $this->merge($this->config, $dbconf);
			}
			$cache->put('config', $this->config);
		} catch(Exception $ex) {
		}
	}

	/**
	 * Set configuration item value
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value) {

		$this->load();
		$this->config[$key] = $value;
		$this->modified = true;
	}

	/**
	 * Get configuration array item value
	 *
	 * @param string $key
	 * @param string $arrayKey
	 * @return mixed
	 */
	public function getValue($key, $arrayKey) {

		$this->load();
		return isset($this->config[$key][$arrayKey]) ? $this->config[$key][$arrayKey] : null;
	}

	/**
	 * Set configuration array item value
	 *
	 * @param string $key
	 * @param string $arrayKey
	 * @param mixed  $arrayValue
	 */
	public function setValue($key, $arrayKey, $arrayValue) {

		$this->load();
		if(!isset($this->config[$key])) {
			$this->config[$key] = array();
		}
		$this->config[$key][$arrayKey] = $arrayValue;
		$this->modified = true;
	}

	/**
	 * Get full configuration
	 *
	 * @return array
	 */
	public function getConfig() {

		$this->load();
		return $this->config;
	}

	/**
	 * Get modified configuration items
	 *
	 * @return array
	 */
	public function getDiff() {

		$this->load();
		return $this->diff();
	}

	/**
	 * Reset configuration changes
	 */
	public function reset() {

		$this->load();
		$this->config = array();
		$this->modified = true;
		$this->save();
	}
}
