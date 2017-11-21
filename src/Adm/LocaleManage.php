<?php

namespace Difra\Adm;

use Difra\Envi\Action;
use Difra\Resourcer;
use Difra\Resourcer\Locale;

/**
 * Class Localemanage
 * @package Difra\Adm
 */
class LocaleManage
{

    /**
     * Singleton
     * @return LocaleManage
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Get locales information as XML
     * @param \DOMElement|\DOMNode $node
     */
    public function getLocalesXML($node)
    {
        $locales = $this->getLocales();

        foreach ($locales as $localeName => $items) {
            foreach ($items as $i => $data) {
                $data['path'] = preg_replace('|^/locale/|u', '', $data['path']);
                $data['value'] = preg_replace('|^<!--\s*atomic\s*-->|u', '', $data['value']);

                $attributes = [];
                foreach ($data['attributes'] as $key => $value) {
                    $attributes[] = $key . '="' . addcslashes($value, '"') . '"';
                }

                $data['attributes-as-string'] = implode(' ', $attributes);

                $data['key'] = $data['path'] . ' ' . $data['attributes-as-string'];

                $locales[$localeName][$i] = $data;
            }
        }

        foreach ($locales as $localeName => $items) {
            /** @var \DOMElement $localeNode */
            $localeNode = $node->appendChild($node->ownerDocument->createElement('locale'));
            $localeNode->setAttribute('name', $localeName);

            foreach ($items as $data) {
                /** @var \DOMElement $itemNode */
                $itemNode = $localeNode->appendChild($localeNode->ownerDocument->createElement('item'));
                $itemNode->setAttribute('path', $data['path']);
                $itemNode->setAttribute('value', $data['value']);
                $itemNode->setAttribute('key', $data['key']);
                $itemNode->setAttribute('attributes', $data['attributes-as-string']);
            }
        }

        $all = [];
        foreach ($locales as $items) {
            foreach ($items as $data) {
                $all[$data['key']] = [
                    'key' => $data['key'],
                    'path' => $data['path'],
                    'attributes' => $data['attributes-as-string'],
                ];
            }
        }

        /** @var \DOMElement $allNode */
        $allNode = $node->appendChild($node->ownerDocument->createElement('all'));
        foreach ($all as $data) {
            /** @var \DOMElement $itemNode */
            $itemNode = $allNode->appendChild($allNode->ownerDocument->createElement('item'));
            $itemNode->setAttribute('key', $data['key']);
            $itemNode->setAttribute('path', $data['path']);
            $itemNode->setAttribute('attributes', $data['attributes']);
        }
    }

    /**
     * Get locales information as array
     * @return array
     */
    public function getLocales()
    {
        $locales = [];
        foreach (Locale::getInstance()->findInstances() as $instance) {
            $xml = new \DOMDocument();
            $xml->preserveWhiteSpace = false;
            $xml->loadXML(Resourcer::getInstance('locale')->compile($instance));
            $locales[$instance] = [];
            $this->getItems($xml->documentElement, $locales[$instance]);
        }

        return $locales;
    }

    /**
     * @param \DOMElement $node
     * @param array $arr
     * @param string $xpath
     */
    private function getItems(\DOMElement $node, array &$arr, $xpath = null)
    {
        $xpath .= '/' . $node->nodeName;

        $attributes = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }
        }

        $isTextNode = false;
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $isTextNode = true;

                break;
            }

            if ($child->nodeType === XML_COMMENT_NODE) {
                if (trim($child->textContent) === 'atomic') {
                    $isTextNode = true;

                    break;
                }
            }
        }

        if ($isTextNode) {
            $xml = '';
            foreach ($node->childNodes as $child) {
                $xml .= $node->ownerDocument->saveXml($child);
            }

            $arr[] = [
                'path' => $xpath,
                'attributes' => $attributes,
                'value' => $xml,
            ];

            return;
        }

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $arr[] = [
                    'path' => $xpath,
                    'attributes' => $attributes,
                    'value' => $node->nodeValue,
                ];

                continue;
            }

            if ($child->nodeType === XML_COMMENT_NODE) {
                continue;
            }

            $this->getItems($child, $arr, $xpath);
        }
    }

    /**
     * todo
     * Try to detect locale record usages
     * @param string $xpath
     * @return int
     * @throws \Difra\Exception
     */
    public function findUsages($xpath)
    {
        static $cache = [];
        if (isset($cache[$xpath])) {
            return $cache[$xpath];
        }
        static $templates = null;
        if (is_null($templates)) {
            $resourcer = Resourcer::getInstance('xslt');
            $types = $resourcer->findInstances();
            foreach ($types as $type) {
                $templates[$type] = $resourcer->compile($type);
            }
        }
        $matches = 0;
        foreach ($templates as $tpl) {
            $matches += substr_count($tpl, '"$locale/' . $xpath . '"');
            $matches += substr_count($tpl, '{$locale/' . $xpath . '}');
        }
        static $menus = null;
        if (is_null($menus)) {
            $resourcer = Resourcer::getInstance('menu');
            $types = $resourcer->findInstances();
            foreach ($types as $type) {
                $menus[$type] = $resourcer->compile($type);
            }
        }
        foreach ($menus as $tpl) {
            $matches += substr_count($tpl, 'xpath="locale/' . $xpath . '"');
        }
        static $controllers = null;
        if (is_null($controllers)) {
            $controllers = [];
            foreach (Action::getControllerPaths() as $dir) {
                $this->getAllFiles($controllers, $dir);
            }
        }
        foreach ($controllers as $controller) {
            $matches += substr_count($controller, "'" . $xpath . "'");
            $matches += substr_count($controller, '"' . $xpath . '"');
        }
        return $cache[$xpath] = $matches;
    }

    /**
     * todo
     * Get all locale files from directory (recursive)
     * @param string[] $collection
     * @param string $dir
     */
    private function getAllFiles(array &$collection, $dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $d = opendir($dir);
        while ($f = readdir($d)) {
            $df = $dir . '/' . $f;
            if ($f{0} == '.') {
                continue;
            }
            if (is_dir($df)) {
                $this->getAllFiles($collection, $df);
            } else {
                $collection[trim($df, '/')] = file_get_contents($df);
            }
        }
    }
}
