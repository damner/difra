<?php

namespace Difra;

use Difra\Envi\Setup;

/**
 * Class Locales
 * @package Difra
 */
class Locales
{
    /** @var string Default locale */
    public $locale = 'en_US';
    /**
     * @var \DOMDocument
     */
    public $localeXML = null;
    // TODO: replace this values with locale's built in methods?
    /** @var array Date formats */
    public $dateFormats = ['ru_RU' => 'd.m.y', 'en_US' => 'm-d-y'];
    /** @var array Date and time formats */
    public $dateTimeFormats = ['ru_RU' => 'd.m.y H:i:s', 'en_US' => 'm-d-y h:i:s A'];
    /** @var bool Locale is loaded flag */
    private $loaded = false;

    /**
     * Constructor
     * @param $locale
     */
    private function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Forbid cloning
     */
    private function __clone()
    {
    }

    /**
     * @param \DOMElement[] $nodes
     * @param string|null $field1
     * @param string|null $value1
     * @param string|null $field2
     * @param string|null $value2
     * @param string|null $field3
     * @param string|null $value3
     * @param string|null $field4
     * @param string|null $value4
     * @return string
     */
    public static function l10n(array $nodes, $field1 = null, $value1 = null, $field2 = null, $value2 = null, $field3 = null, $value3 = null, $field4 = null, $value4 = null)
    {
        $values = [];
        foreach ([$field1 => $value1, $field2 => $value2, $field3 => $value3, $field4 => $value4] as $name => $value) {
            if ($name === '') {
                continue;
            }

            $values[$name] = $value;
        }

        return (string)self::getInDomNodes($nodes, $values);
    }

    /**
     * Get text string from current locale
     * @param string $name Example: 'auth/register/login_invalid' or '/locale/auth/register/login_invalid' (old)
     * @param array $values
     * @return string|null
     */
    public static function get($name, array $values = [])
    {
        $nodes = self::getInstance()->getXPath($name);

        return self::getInDomNodes($nodes, $values);
    }

    /**
     * @param \DOMElement[] $nodes
     * @param array $values
     * @return null|string
     */
    private static function getInDomNodes(array $nodes, array $values)
    {
        if (!count($nodes)) {
            return null;
        }

        $values2 = [];
        foreach ($values as $name1 => $value) {
            $name2 = preg_replace('|^filter-|', '', $name1);
            $name3 = preg_replace('|^plural-|', '', $name1);

            $name = $name2 !== $name1 ? $name2 : ($name3 !== $name1 ? $name3 : $name1);

            if (is_array($value)) {
                $value = array_reduce($value, function ($result, $value) {
                    if ($value instanceof \DOMAttr) {
                        $value = $value->value;
                    }
                    return $result . $value;
                }, '');
            }

            $values2[$name] = [
                'filter' => $name2 !== $name1,
                'plural' => $name3 !== $name1,
                'value' => $value,
            ];
        }

        $filter = [];

        foreach ($values2 as $name => $data) {
            if ($data['filter']) {
                $filter[$name] = $data['value'];
            }
        }

        foreach ($values2 as $name => $data) {
            if ($data['plural']) {
                $filter['plural-' . $name] = self::getPluralN($data['value']);
            }
        }

        $nodes = array_filter($nodes, function (\DOMElement $node) use ($filter) {
            foreach ($filter as $name => $value) {
                if ((string)$value !== $node->getAttribute($name)) {
                    return false;
                }
            }

            return true;
        });

        if (!count($nodes)) {
            return null;
        }

        $xml = '';
        foreach (end($nodes)->childNodes as $childNode) {
            $xml .= $childNode->ownerDocument->saveXml($childNode);
        }

        if (count($values2)) {
            $xml = preg_replace_callback('|<v-([a-zA-Z0-9-]+)\s*/>|u', function (array $matches) use ($values2) {
                if (array_key_exists($matches[1], $values2)) {
                    return htmlspecialchars($values2[$matches[1]]['value'], ENT_QUOTES | ENT_HTML5);
                }

                return $matches[0];
            }, $xml);
        }

        return $xml;
    }

    /**
     * @param int $n
     * @return int
     */
    private static function getPluralN($n)
    {
        $n = abs($n);

        $lang = self::getInstance()->locale;

        if ($lang === 'ru_RU') {
            return $n % 10 === 1 && $n % 100 !== 11 ? 1 : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? 2 : 5);
        }

        if ($lang === 'en_US') {
            return $n === 1 ? 1 : 2;
        }

        return 1;
    }

    /**
     * Get locale string by XPath
     * @param string $name Example: 'auth/register/login_invalid' or '/locale/auth/register/login_invalid' (old)
     * @return \DOMElement[]
     * @throws Exception
     */
    private function getXPath($name)
    {
        // Backward compatibility
        $name = preg_replace('|^/locale/|u', '', $name);

        $this->load();

        $xpath = new \DOMXPath($this->localeXML);

        $nodes = $xpath->query($name, $this->localeXML->documentElement);
        if ($nodes === false) {
            if (Debugger::isEnabled()) {
                throw new Exception(sprintf('Bad XPath expression "%s"', $name));
            }

            return [];
        }

        return iterator_to_array($nodes);
    }

    /**
     * Load locale resource
     */
    private function load()
    {
        if (!$this->loaded) {
            $xml = Resourcer::getInstance('locale')->compile($this->locale);
            $this->localeXML = new \DOMDocument();
            $this->localeXML->loadXML($xml);
        }
    }

    /**
     * Singleton
     * @param null $locale
     * @return Locales
     */
    public static function getInstance($locale = null)
    {
        static $locales = [];
        if (!$locale) {
            $locale = Setup::getLocale();
        }
        if (isset($locales[$locale])) {
            return $locales[$locale];
        }
        $locales[$locale] = new self($locale);
        return $locales[$locale];
    }

    /**
     * Returns locale as XML document
     * @param \DOMElement $node
     * @return void
     */
    public function getLocaleXML(\DOMElement $node)
    {
        $this->load();
        if (!is_null($this->localeXML)) {
            $node->appendChild($node->ownerDocument->importNode($this->localeXML->documentElement, true));
        }
    }

    /**
     * Set current locale
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Validate date string
     * @param $string
     * @return bool
     */
    public function isDate($string)
    {
        if (!$date = $this->parseDate($string)) {
            return false;
        }
        return checkdate($date[1], $date[2], $date[0]);
    }

    /**
     * Parse date string
     * Returns array [ 0 => Y, 1 => m, 2 => d ]
     * @param string $string
     * @param string|bool $locale
     * @return array|bool
     */
    public function parseDate($string, $locale = false)
    {
        $string = str_replace(['.', '-'], '/', $string);
        $pt = explode('/', $string);
        if (sizeof($pt) != 3) {
            return false;
        }
        // returns $date[year,month,day] depending on current locale and dateFormats.
        $date = [0, 0, 0];
        $localeInd = ['y' => 0, 'm' => 1, 'd' => 2];
        $df = $this->dateFormats[$locale ? $locale : $this->locale];
        $df = str_replace(['-', '.'], '/', $df);
        $localePt = explode('/', $df);
        foreach ($localePt as $ind => $key) {
            $date[$localeInd[$key]] = $pt[$ind];
        }
        // Get 4-digit year number from 2-digit year number
        if ($date[0] < 100) {
            $date[0] = ($date[0] < 70 ? 2000 : 1900) + $date[0];
        }
        return $date;
    }

    /**
     * Convert local date string to MySQL date string
     * @param string $dateString if ommited, current date is used
     * @return string|false
     */
    public function getMysqlDate($dateString = null)
    {
        if (!$dateString) {
            return date('%Y-%m-%d');
        }
        if (!$date = $this->parseDate($dateString)) {
            return false;
        }
        return implode('-', $date);
    }

    /**
     * Get MySQL syntax for getting localized dates
     * @param bool $locale
     * @return mixed
     */
    public function getMysqlFormat($locale = false)
    {
        $localePt = $this->dateFormats[$locale ? $locale : $this->locale];
        $localePt = str_replace(['d', 'm', 'y'], ['%d', '%m', '%Y'], $localePt);
        return $localePt;
    }

    /**
     * Convert MySQL date string to localized date string
     * @param string $date
     * @param boolean $withTime
     * @return string
     */
    public function getDateFromMysql($date, $withTime = false)
    {
        $date = explode(' ', $date);
        $date[0] = explode('-', $date[0]);
        $date[1] = explode(':', $date[1]);

        if ($withTime) {
            return $this->getDateTime(
                mktime($date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0])
            );
        }
        return $this->getDate(mktime($date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0]));
    }

    /**
     * Get localized date and time from timestamp
     * @param $timestamp
     * @return string
     */
    public function getDateTime($timestamp)
    {
        return date($this->dateTimeFormats[$this->locale], $timestamp);
    }

    /**
     * Get localized date from timestamp
     * @param int $timestamp
     * @return string
     */
    public function getDate($timestamp)
    {
        return date($this->dateFormats[$this->locale], $timestamp);
    }

    /**
     * Create link part from string.
     * Used to replace all uncommon characters with dash.
     * @param string $string
     * @return string
     */
    public function makeLink($string)
    {
        $link = '';
        // This string is UTF-8!
        $num = preg_match_all('/[A-Za-zА-Яа-я0-9Ёё]*/u', $string, $matches);
        if ($num and !empty($matches[0])) {
            $matches = array_filter($matches[0], 'strlen');
            $link = implode('-', $matches);
        }
        if ($link == '') {
            $link = '-';
        }
        return mb_strtolower($link);
    }
}
