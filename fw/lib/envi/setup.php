<?php

namespace Difra\Envi;

use Difra\Config;
use Difra\Envi;

/**
 * Class Setup
 * @package Difra\Envi
 */
class Setup
{
    /**
     * Set up environment
     */
    public static function run()
    {
        mb_internal_encoding('UTF-8');
        ini_set('short_open_tag', false);
        ini_set('asp_tags', false);
        ini_set('mysql.trace_mode', false);

        // set session domain
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.cookie_domain', '.' . Envi::getHost(true));

        // set default time zone
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('Europe/Moscow');
        }

        // prepare data
        if (get_magic_quotes_gpc()) {
            $strip_slashes_deep = function ($value) use (&$strip_slashes_deep) {

                return is_array($value) ? array_map($strip_slashes_deep, $value) : stripslashes($value);
            };
            $_GET = array_map($strip_slashes_deep, $_GET);
            $_POST = array_map($strip_slashes_deep, $_POST);
            $_COOKIE = array_map($strip_slashes_deep, $_COOKIE);
        }

        self::setLocale();
    }

    /** @var string Default locale */
    static private $locale = false;

    /**
     * Set locale
     * @param $locale
     */
    public static function setLocale($locale = false)
    {
        if (!$locale) {
            if ($configLocale = Config::getInstance()->get('locale')) {
                $locale = $configLocale;
            }
        }
        self::$locale = $locale ?: 'ru_RU';
        setlocale(LC_ALL, [self::$locale . '.UTF-8', self::$locale . '.utf8']);
        setlocale(LC_NUMERIC, ['en_US.UTF-8', 'en_US.utf8']);
    }

    /**
     * Get locale name
     * @return string
     */
    public static function getLocale()
    {
        return self::$locale;
    }
}
