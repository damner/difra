<?php

namespace Difra;

use Difra\Envi\Setup;

/**
 * Class Locales
 * @package Difra
 */
class Locales
{
    /**
     * @var string Default locale
     * @todo Make private
     */
    public $locale = 'en_US';

    /**
     * @var \DOMDocument
     */
    private $localeXML = null;

    /**
     * @var bool Locale is loaded flag
     */
    private $loaded = false;

    /**
     * @param string $locale
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
     * Singleton
     * @param null $locale
     * @return Locales
     */
    public static function getInstance($locale = null)
    {
        static $locales = [];

        $locale = $locale ?? Setup::getLocale();

        if (!isset($locales[$locale])) {
            $locales[$locale] = new self($locale);
        }

        return $locales[$locale];
    }

    /**
     * Load locale resource
     */
    private function load()
    {
        if (!$this->loaded) {
            $this->loaded = true;

            $xml = Resourcer::getInstance('locale')->compile($this->locale);
            $this->localeXML = new \DOMDocument();
            $this->localeXML->loadXML($xml);
        }
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
     * @return \DOMElement|null
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

        return self::getNode($nodes, $values);
    }

    /**
     * Get text string from current locale
     * @param string $name Example: 'default/title' or '/locale/default/title' (old)
     * @param array $values
     * @return string|null
     */
    public static function get($name, array $values = [])
    {
        $nodes = self::getInstance()->getXPath($name);

        $node = self::getNode($nodes, $values);

        if ($node === null) {
            return null;
        }

        $xml = '';
        foreach ($node->childNodes as $childNode) {
            $xml .= $node->ownerDocument->saveXML($childNode);
        }

        return $xml;
    }

    /**
     * @param \DOMElement[] $nodes
     * @param array $values
     * @return \DOMElement|null
     */
    private static function getNode(array $nodes, array $values)
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
                    if ($value instanceof \DOMNode) {
                        $value = $value->nodeValue;
                    }
                    return $result . $value;
                }, '');
            }

            $values2[] = [
                'name' => $name,
                'filter' => $name2 !== $name1,
                'plural' => $name3 !== $name1,
                'value' => (string)$value,
            ];
        }

        $filter = [];

        foreach ($values2 as $data) {
            if ($data['filter']) {
                $filter[$data['name']] = $data['value'];
            }
        }

        foreach ($values2 as $data) {
            if ($data['plural']) {
                $filter['plural-' . $data['name']] = self::getPluralN($data['value']);
            }
        }

        if (count($filter)) {
            $nodes = array_filter($nodes, function (\DOMElement $node) use ($filter) {
                foreach ($filter as $name => $value) {
                    if ($value !== $node->getAttribute($name)) {
                        return false;
                    }
                }

                return true;
            });
        }

        if (!count($nodes)) {
            return null;
        }

        $doc = new \DOMDocument('1.0', 'utf-8');

        /** @var \DOMElement $node */
        $node = $doc->appendChild($doc->importNode(end($nodes), true));

        return self::interpolateNodeValues($node, array_column($values2, 'value', 'name'));
    }

    /**
     * @param \DomElement $node
     * @param array $values
     * @return \DOMElement
     */
    private static function interpolateNodeValues(\DomElement $node, array $values)
    {
        if (!count($values)) {
            return $node;
        }

        $xpath = new \DOMXPath($node->ownerDocument);
        $nodes = $xpath->query('//*[starts-with(name(), "v-")]');

        foreach ($nodes as $valueNode) {
            /** @var \DOMElement $valueNode */
            $name = mb_substr($valueNode->nodeName, 2);
            if (array_key_exists($name, $values)) {
                $valueNode->parentNode->replaceChild($node->ownerDocument->createTextNode($values[$name]), $valueNode);
            }
        }

        return $node;
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
     * Get array of nodes
     * @param string $name Example: 'default/title' or '/locale/default/title' (old)
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

}
