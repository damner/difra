<?php

namespace Difra;

use Difra\DB\Adapters\MySQL;
use Difra\DB\Adapters\Sqlite;

/**
 * Factory for DB
 * Class DB
 * @package Difra
 */
class DB
{
    /** @var array Configuration instances */
    private static $adapters = [];

    /**
     * @param string $instance
     * @return MySQL|Sqlite
     * @throws \Difra\Exception
     */
    public static function getInstance($instance = 'default')
    {
        if (isset(self::$adapters[$instance])) {
            // TODO: ping db
            return self::$adapters[$instance];
        }

        $cfg = self::getConfig();
        if (!isset($cfg[$instance]) and $instance != 'default') {
            return self::$adapters[$instance] = self::getInstance();
        }
        switch (strtolower($cfg[$instance]['type'])) {
            case 'mysql':
                return self::$adapters[$instance] = new MySQL($cfg[$instance]);
            case 'sqlite':
                return self::$adapters[$instance] = new Sqlite($cfg[$instance]);
            default:
                throw new Exception("PDO adapter not found for '{$cfg[$instance]['type']}'");
        }
    }

    /**
     * Get configuration
     * @return mixed
     */
    private static function &getConfig()
    {
        static $cfg = null;
        if (!is_null($cfg)) {
            return $cfg;
        }

        $cfg = Config::getInstance()->get('db');

//        // generate default config + backwards compatibility
//        if (empty($cfg) or empty($cfg['default'])) {
//            $cfg['default'] = [];
//        }
//        $keys = ['type', 'hostname', 'database', 'username', 'password'];
//        foreach ($keys as $key) {
//            if (!isset($cfg['default'][$key])) {
//                if (isset($cfg[$key])) {
//                    $cfg['default'][$key] = $cfg[$key];
//                    unset($cfg[$key]);
//                } else {
//                    switch ($key) {
//                        case 'type':
//                            $cfg['default']['type'] = 'mysql';
//                            break;
//                        case 'database':
//                        case 'username':
//                            $cfg['default'][$key] = Envi::getSubsite();
//                            break;
//                        default:
//                            $cfg['default'][$key] = '';
//                    }
//                }
//            }
//        }
//
//        // add missing keys from default config
//        foreach ($cfg as $name => &$conf) {
//            foreach ($keys as $key) {
//                $conf['name'] = $name;
//                if (!isset($conf[$key])) {
//                    $conf[$key] = $cfg['default'][$key];
//                }
//            }
//        }

        return $cfg;
    }
}
