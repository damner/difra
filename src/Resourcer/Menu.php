<?php

namespace Difra\Resourcer;

use Difra\Debugger;

/**
 * Class Menu
 * @package Difra\Resourcer
 */
class Menu extends Abstracts\XML
{
    /** @var string Menu resourcer */
    protected $type = 'menu';
    /** @var bool Don't view resource */
    protected $printable = false;

    /**
     * @param \SimpleXMLElement $xml
     * @param string $instance
     */
    protected function postprocess(\SimpleXMLElement $xml, $instance)
    {
        $xml->addAttribute('instance', $instance);
        /** @noinspection PhpUndefinedFieldInspection */
        if ($xml->attributes()->prefix) {
            /** @noinspection PhpUndefinedFieldInspection */
            $href = $xml->attributes()->prefix;
        } else {
            $href = '/' . $instance;
        }
        $this->recursiveProcessor($xml, $href, 'menu', $instance);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $href
     * @param string $prefix
     * @param string $instance
     */
    private function recursiveProcessor(\SimpleXMLElement $node, $href, $prefix, $instance)
    {
        /** @var \SimpleXMLElement $subNode */
        foreach ($node as $subname => $subNode) {
            /** @noinspection PhpUndefinedFieldInspection */
            if ($subNode->attributes()->sup and $subNode->attributes()->sup == '1') {
                if (!Debugger::isEnabled()) {
                    $subNode->addAttribute('hidden', 1);
                }
            }
            $newHref = $href . '/' . $subname;
            $newPrefix = $prefix . '_' . $subname;
            $subNode->addAttribute('id', $newPrefix);
            /** @noinspection PhpUndefinedFieldInspection */
            if (!isset($subNode->attributes()->href)) {
                $subNode->addAttribute('href', $newHref);
            }
            $subNode->addAttribute('pseudoHref', $newHref);
            $subNode->addAttribute('xpath', 'locale/menu/' . $instance . '/' . $newPrefix);
            $this->recursiveProcessor($subNode, $newHref, $newPrefix, $instance);
        }
    }
}
