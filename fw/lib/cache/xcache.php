<?php

namespace Difra\Cache;

use Difra;

/**
 * Реализация кэширования через расширение xcache
 * Class XCache
 * @package Difra\Cache
 */
class XCache extends Common {

	public $adapter = 'XCache';

	/**
	 * Определяет работоспособность расширения
	 * @return bool
	 */
	public static function isAvailable() {

		try {
			if( !extension_loaded( 'xcache' ) or !ini_get( 'xcache.var_size' ) ) {
				return false;
			}
			@xcache_isset( 'test' );
			if( $e = error_get_last() and $e['file'] == __FILE__ ) {
				return false;
			}
		} catch( Difra\Exception $ex ) {
			return false;
		}
		return true;
	}

	/**
	 * Получение данных из кэша
	 * @param string  $id
	 * @param boolean $doNotTestCacheValidity
	 *
	 * @return string
	 */
	public function realGet( $id, $doNotTestCacheValidity = false ) {

		if( xcache_isset( $id ) ) {
			return xcache_get( $id );
		}
		return null;
	}

	/**
	 * Проверка существования ключа
	 * @param string $id cache id
	 *
	 * @return boolean
	 */
	public function test( $id ) {

		return xcache_isset( $id );
	}

	/**
	 * Сохранение данных в кэше
	 * @param string   $id
	 * @param string   $data
	 * @param bool|int $specificLifetime
	 *
	 * @return boolean
	 */
	public function realPut( $id, $data, $specificLifetime = false ) {

		return xcache_set( $id, $data, $specificLifetime );
	}

	/**
	 * Удаление данных
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function realRemove( $id ) {

		return xcache_unset( $id );
	}

	/**
	 * Определяет наличие автоматической подчистки кэша
	 * @return boolean
	 */
	public function isAutomaticCleaningAvailable() {

		return true;
	}
}
