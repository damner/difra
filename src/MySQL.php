<?php

namespace Difra;

use Difra\MySQL\Abstracts\MySQLi;
use Difra\MySQL\Abstracts\None;

/**
 * Factory for MySQL
 * Deprecated: please use PDO.
 * Class MySQL
 * @package Difra
 * @deprecated
 */
class MySQL
{
    /** Auto detect adapter */
    const INST_AUTO = 'auto';
    /** MySQLi */
    const INST_MYSQLI = 'MySQLi';
    /** Stub */
    const INST_NONE = 'none';
    /** Default adapter */
    const INST_DEFAULT = self::INST_AUTO;
    /** @var array Adapters registry */
    private static $adapters = [];

    /**
     * @param string $adapter
     * @param bool $new
     * @return MySQL\Abstracts\MySQLi|MySQL\Abstracts\None
     */
    public static function getInstance($adapter = self::INST_DEFAULT, $new = false)
    {
        if ($adapter == self::INST_AUTO) {
            static $auto = null;
            if (!is_null($auto)) {
                return self::getInstance($auto, $new);
            }

            if (MySQLi::isAvailable()) {
                Debugger::addLine("MySQL module: MySQLi");
                return self::getInstance($auto = self::INST_MYSQLI, $new);
            } else {
                Debugger::addLine("No suitable MySQL module detected");
                return self::getInstance($auto = self::INST_NONE, $new);
            }
        }

        if (!$new and isset(self::$adapters[$adapter])) {
            return self::$adapters[$adapter];
        }

        switch ($adapter) {
            case self::INST_MYSQLI:
                $obj = new MySQLi();
                break;
            default:
                $obj = new None();
        }
        if (!$new) {
            self::$adapters[$adapter] = $obj;
        }
        return $obj;
    }
}
