<?php

namespace Difra\Unify;

use Difra\Exception;

/**
 * Class Table
 * @package Difra\Unify
 */
class Table extends Storage
{
    /** @var array[string $name] Object properties definitions */
    static protected $propertiesList = null;
    /** @var string|string[] Column or column list for Primary Key */
    static protected $primary = null;
    /** @var string|null Name of database table */
    static protected $tableName = null;

    /**
     * Returns table name
     * @return string
     */
    public static function getTable()
    {
        static $table = null;
        if (!is_null($table)) {
            return $table;
        }
        if (isset(static::$tableName)) {
            return $table = static::$tableName;
        }
        return $table = mb_strtolower(implode('_', static::getClassParts()));
    }

    /**
     * Chops namespace and class into parts without common pieces
     * @return array
     * @throws Exception
     */
    protected static function getClassParts()
    {
        static $parts = null;
        if (!is_null($parts)) {
            return $parts;
        }
        $parts = explode('\\', $class = get_called_class());
        if (sizeof($parts) < 4 or $parts[0] != 'Difra' or $parts[1] != 'Plugins' or $parts[3] != 'Objects') {
            throw new Exception('Bad object class name: ' . $class);
        }
        unset($parts[3]);
        unset($parts[1]);
        unset($parts[0]);
        return $parts;
    }

    /**
     * Returns column name or list of column names for Primary Key
     * @return string|string[]
     */
    public static function getPrimary()
    {
        static $primary = null;
        if (!is_null($primary)) {
            return $primary;
        }
        if (static::$primary) {
            return $primary = static::$primary;
        }
        if (!empty(static::$propertiesList)) {
            foreach (static::$propertiesList as $name => $desc) {
                if (!is_array($desc)) {
                    continue;
                }
                if (isset($desc['primary']) and $desc['primary']) {
                    return $primary = $name;
                }
            }
        }
        return $primary = false;
    }

    /** @var string[] List of supported key types */
    static private $keyTypes = [
        'index',
        'primary',
        'unique',
        'fulltext',
        'foreign'
    ];

    /**
     * Get list of columns from self::$propertiesList
     * @return array
     */
    protected static function getColumns()
    {
        static $result = null;
        if (!is_null($result)) {
            return $result;
        }
        $result = [];
        foreach (static::$propertiesList as $name => $prop) {
            $type = !is_array($prop) ? $prop : $prop['type'];
            if (!in_array($type, self::$keyTypes)) {
                $result[$name] = $prop;
            }
        }
        return $result;
    }

    /**
     * Get list of indexes from self::$propertiesList
     * @return array
     */
    protected static function getIndexes()
    {
        $result = null;
        if (!is_null($result)) {
            return $result;
        }
        $result = [];
        if ($primary = static::getPrimary()) {
            $result['PRIMARY'] = ['type' => 'primary', 'columns' => $primary];
        }
        foreach (static::$propertiesList as $name => $prop) {
            if (!is_array($prop)) {
            } elseif (in_array($prop['type'], self::$keyTypes)) {
                $result[$name] = $prop;
            } else {
                foreach (self::$keyTypes as $keyType) {
                    if ($keyType == 'primary') {
                        continue;
                    }
                    if (isset($prop[$keyType]) and $prop[$keyType]) {
                        $result[$name] = ['type' => $keyType, 'columns' => $name];
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get properties definitions
     * @return array
     */
    public static function getPropertiesList()
    {
        return self::$propertiesList;
    }
}
