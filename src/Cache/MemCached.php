<?php

namespace Difra\Cache;

use Difra\Cache;
use Difra\Exception;

/**
 * Memcached (memcached module) adapter
 * Class MemCached
 * @package Difra\Cache
 */
class MemCached extends Common
{
    /** @var \Memcached */
    private static $memcache = null;
    /** @var string Serialize data flag */
    private static $serialize = true;
    /** @var int TTL */
    private static $lifetime = 0;
    /** @var string Adapter name */
    public $adapter = Cache::INST_MEMCACHED;

    /**
     * Detect if backend is available
     * @return bool
     */
    public static function isAvailable()
    {
        try {
            if (!is_null(self::$memcache)) {
                return self::$memcache ? true : false;
            }
            if (!extension_loaded('memcached')) {
                return self::$memcache = false;
            }

            $memcache = new \MemCached;
            // todo: load from config
            $memcache->addServer('127.0.0.1', '11211');
            // if ($memcache->getStats() < 0) { // returns ['127.0.0.1:11211'=>['pid'=>-1,...]]
            //     return self::$memcache = false;
            // }
            self::$memcache = $memcache;

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Get cache record implementation
     * @param string $id
     * @param bool $doNotTestCacheValidity
     * @return mixed|null
     */
    public function realGet($id, $doNotTestCacheValidity = false)
    {
        $data = @self::$memcache->get($id);
        return self::$serialize ? @unserialize($data) : $data;
    }

    /**
     * Test if cache record exists implementation
     * @param string $id
     * @return bool
     */
    public function test($id)
    {
        $data = $this->get($id);
        return !empty($data);
    }

    /**
     * Put cache record implementation
     * @param string $id
     * @param mixed $data
     * @param bool $specificLifetime
     * @return bool
     */
    public function realPut($id, $data, $specificLifetime = false)
    {
        return self::$memcache->set(
            $id,
            self::$serialize ? serialize($data) : $data,
            $specificLifetime !== false ? $specificLifetime : self::$lifetime
        );
    }

    /**
     * Delete cache record implementation
     * @param string $id
     * @return bool
     */
    public function realRemove($id)
    {
        return @self::$memcache->delete($id);
    }

    /**
     * Define automatic cache cleaning as available
     * @return bool
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }
}
