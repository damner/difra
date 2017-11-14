<?php

namespace Difra\Resourcer\Abstracts;

/**
 * Class XSLT
 * @package Difra\Resourcer\Abstracts
 */
abstract class XSLT extends Common
{
    /**
     * @inheritdoc
     */
    protected function processData($instance)
    {
        /*
        <!DOCTYPE xsl:stylesheet [
        <!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-lat1.ent">
        <!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-symbol.ent">
        <!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-special.ent">
        %lat1;
        %symbol;
        %special;
        ]>
        */

        $files = $this->getFiles($instance);
        $template = '<?xml version="1.0" encoding="UTF-8"?>
				<!DOCTYPE xsl:stylesheet>
				<xsl:stylesheet	version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
					<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes"/>
					<xsl:param name="locale" select="/root/locale"/>
					<xsl:template match="/root/locale"/>
				</xsl:stylesheet>';
        $dom = new \DOMDocument();
        $dom->loadXML($template);

        $namedNodes = [];
        $namespaces = [];
        foreach ($files as $filename) {
            $template = new \DOMDocument();
            $template->load($filename['raw']);
            $xpath = new \DOMXPath($template);
            foreach ($xpath->query('namespace::*', $template->documentElement) as $nsNode) {
                $namespaces[$nsNode->nodeName] = $nsNode->nodeValue;
            }
            /** @var \DOMElement $child */
            foreach ($template->documentElement->childNodes as $child) {
                switch ($child->nodeType) {
                    case XML_TEXT_NODE:
                    case XML_COMMENT_NODE:
                        continue 2;
                }

                if ($child->nodeName === 'xsl:output') {
                    continue;
                }

                if ($child->nodeName === 'xsl:template') {
                    $child->setAttribute('from-file', $filename['raw']);

                    if ($child->hasAttribute('name')) {
                        $name = $child->getAttribute('name');
                        $mode = $child->getAttribute('mode');

                        $node = $dom->importNode($child, true);

                        $key = $name . ':' . $mode;
                        $namedNodes[$key][] = $node;

                        /** @var \DOMElement $oldNode */
                        foreach (array_slice($namedNodes[$key], 0, -1) as $oldNode) {
                            $oldNode->setAttribute('name', $oldNode->getAttribute('name') . '--redeclared');
                        }

                        $dom->documentElement->appendChild($node);

                        continue;
                    }
                }

                $dom->documentElement->appendChild($dom->importNode($child, true));
            }
        }

        if (!empty($namespaces)) {
            foreach ($namespaces as $k => $v) {
                $dom->documentElement->setAttribute($k, $v);
            }
        }

        return $dom->saveXML();
    }
}
