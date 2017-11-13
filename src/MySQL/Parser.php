<?php

namespace Difra\MySQL;

use Difra\Envi\Roots;

/**
 * Class Parser
 * @package Difra\MySQL
 * @deprecated
 */
class Parser
{
    /**
     * Compare current mysql tables and bin/db.sql files. Return result to XML.
     * @param $node
     */
    public static function getStatusXML($node)
    {
        $classList = [];
        $currentChunks = self::chop(self::getCurrentSQL());
        if (!empty($currentChunks)) {
            foreach ($currentChunks as $chunks) {
                if ($class = self::getChunksClass($chunks)) {
                    $classList[$class] = 1;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $class::create($chunks);
                }
            }
        }
        $goalChunks = self::chop(self::getGoalSQL());
        if (!empty($goalChunks)) {
            foreach ($goalChunks as $chunks) {
                if ($class = self::getChunksClass($chunks)) {
                    $classList[$class] = 1;
                    if (method_exists($class, 'autoGoal')) {
                        $class::autoGoal($chunks);
                    }
                }
            }
        }
        if (!empty($classList)) {
            foreach ($classList as $class => $v) {
                $list = $class::getList();
                foreach ($list as $item) {
                    $item->getStatusXML($node);
                }
            }
        }
    }

    /**
     * Get contents of bin/db.sql files
     * @return string
     */
    public static function getGoalSQL()
    {
        $paths = Roots::get();
        $tables = [];
        foreach ($paths as $path) {
            if (is_readable($path . '/bin/db.sql')) {
                $tables[] = file_get_contents($path . '/bin/db.sql');
            }
            if (is_dir($path . '/bin/db')) {
                $files = scandir($path . '/bin/db');
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (is_readable($path . '/bin/db/' . $file) and $file{0} !== '.') {
                            $tables[] = file_get_contents($path . '/bin/db/' . $file);
                        }
                    }
                }
            }
        }
        return implode("\n", $tables);
    }

    /**
     * Get current tables as SHOW CREATE TABLE text
     * @param bool $asArray
     * @return string|array
     */
    public static function getCurrentSQL($asArray = false)
    {
        $db = \Difra\DB::getInstance();
        $tables = $db->fetchColumn('SHOW TABLES');
        if (empty($tables)) {
            return false;
        }
        $tablesSQL = [];
        foreach ($tables as $table) {
            $t = $db->fetchRow("SHOW CREATE TABLE `$table`");
            $tablesSQL[] = array_pop($t);
        }
        if ($asArray) {
            return $tablesSQL;
        } else {
            return implode(";\n", $tablesSQL);
        }
    }

    /**
     * Chop SQL string to elements
     * @param string $text SQL string
     * @param bool $tree Put grouped elements in braces as a child array
     * @param bool $recursive Internal paramter, do not use
     * @return array
     */
    private static function chop($text, $tree = false, $recursive = false)
    {
        $lines = [];
        $shards = [];
        $next = '';
        $i = 0;
        $size = mb_strlen($text);
        $comm = $linecomm = false;
        $str = '';
        while ($i < $size) {
            $a = mb_substr($text, $i, 1);
            $a1 = ($i < $size - 1) ? mb_substr($text, $i + 1, 1) : '';
            $i++;

            /**
             * Quoted texts
             */

            // end of quoted text?
            if ($str !== '') {
                $str .= $a;
                if (mb_substr($str, 0, 1) == $a and mb_substr($str, -1) != '\\') {
                    $shards[] = $str;
                    $str = '';
                }
                continue;
            }
            // start of quoted text?
            if (!$comm and !$linecomm) {
                if ($a == '"' or $a == "'" or $a == '`') {
                    if ($next !== '') {
                        $shards[] = $next;
                        $next = '';
                    }
                    $str = $a;
                    continue;
                }
            }

            /**
             * Comments
             */

            // end of multiline comment?
            if ($comm) {
                if ($a == '*' and $a1 == '/') {
                    $comm = false;
                    $i++;
                }
                continue;
            }
            // start of multiline comment?
            if ($a == '/' and $a1 == '*') {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                $comm = true;
                $i++;
                continue;
            }
            // end of single line comment?
            if ($linecomm) {
                if ($a == "\n") {
                    $linecomm = false;
                }
                continue;
            }
            // start of single line '--' comment?
            if ($a == '-' and $a1 == '-' and \Difra\Libs\Strings::isWhitespace(mb_substr($text, $i + 1, 1))) {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                $linecomm = true;
                $i += 2;
                continue;
            }
            // start of single line '#' comment?
            if ($a == '#') {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                $linecomm = true;
                continue;
            }

            // bracket starts group?
            if ($a == '(') {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                $res = self::chop(mb_substr($text, $i), $tree, true);
                if ($tree) {
                    $shards[] = $res['data'];
                } else {
                    $shards = array_merge($shards, ['('], $res['data'], [')']);
                }
                $i += $res['parsed'];
                continue;
            }

            // bracket ends group?
            if ($recursive and $a == ')') {
                if ($next !== '') {
                    $shards[] = $next;
                    //$next = '';
                }
                return ['data' => $shards, 'parsed' => $i];
            }

            if (!$recursive and $a == ';') {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                if (!empty($shards)) {
                    $lines[] = $shards;
                }
                $shards = [];
            } elseif ($a == ',' or $a == ';' or $a == '=') {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                }
                $shards[] = $a;
                continue;
            } elseif (!\Difra\Libs\Strings::isWhitespace($a)) {
                $next .= $a;
                continue;
            } else {
                if ($next !== '') {
                    $shards[] = $next;
                    $next = '';
                    continue;
                }
            }
        }
        if ($next !== '') {
            $shards[] = $next;
        }
        if ($recursive) {
            return $shards;
        }
        if (!empty($lines)) {
            $lines[] = $shards;
        }
        return $lines;
    }

    /**
     * Get class for chopped SQL
     * @param $chunks
     * @return string|null
     */
    public static function getChunksClass($chunks)
    {
        if (sizeof($chunks) >= 2 and $chunks[0] == 'CREATE' and $chunks[1] == 'TABLE') {
            return '\Difra\MySQL\SQL\Table';
        }
        return null;
    }

    private static $sep = [',' => ', ', ')' => ') ', '(' => ' ('];

    /**
     * Get text string from chopped SQL
     * @param $array
     * @return string
     */
    public static function def2string($array)
    {
        static $keywords;
        if (!$keywords) {
            $keywords = include(dirname(__FILE__) . '/keywords.php');
        }
        $res = '';
        $d = '';
        foreach ($array as $v) {
            $u = mb_strtoupper($v);
            if (isset($keywords[$u])) {
                switch ($keywords[$u]) {
                    case '1':
                        $a2 = $u;
                        break;
                    default:
                        $a2 = mb_strtolower($v);
                }
            } else {
                $a2 = $v;
            }
            if (isset(self::$sep[$a2])) {
                $res .= self::$sep[$a2];
                $d = '';
            } else {
                $res .= $d . $a2;
                $d = ' ';
            }
        }
        return $res;
    }
}
