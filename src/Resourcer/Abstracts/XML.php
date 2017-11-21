<?php

namespace Difra\Resourcer\Abstracts;

use Difra\Exception;
use Difra\XML as XMLUtils;

/**
 * Abstract adapter for XML resources
 */
abstract class XML extends Common
{
    /**
     * Assemble resources to single XML
     * @param string $instance
     * @return string
     * @throws Exception
     */
    protected function processData($instance)
    {
        $files = $this->getFiles($instance);
        $newXml = new \SimpleXMLElement(sprintf('<?xml version="1.0" encoding="UTF-8"?><%s></%s>', $this->type, $this->type));
        foreach ($files as $file) {
            $internalErrors = libxml_use_internal_errors(true);
            $xml = simplexml_load_file($file['raw']);
            XMLUtils::assertNoLibxmlErrors($internalErrors);
            if ($xml === false) {
                throw new Exception('Unknown error in simplexml_load_file()');
            }
            $this->mergeXML($newXml, $xml);
            foreach ($xml->attributes() as $key => $value) {
                $newXml->addAttribute($key, $value);
            }
        }

        if (method_exists($this, 'postprocess')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->postprocess($newXml, $instance);
        }

        return $newXml->asXML();
    }

    /**
     * Recursively merge two XML trees
     * @param \SimpleXMLElement $xml1
     * @param \SimpleXMLElement $xml2
     */
    private function mergeXML(\SimpleXMLElement $xml1, \SimpleXMLElement $xml2)
    {
        $removed = [];

        $old = [];
        foreach ($xml1->children() as $name => $node) {
            $old[$name][] = clone $node;
        }

        /** @var \SimpleXMLElement $node */
        foreach ($xml2->children() as $name => $node) {
            if (!isset($old[$name])) {
                $subNode = $xml1->addChild($name);

                // Set node content (text)
                dom_import_simplexml($subNode)->textContent = trim((string)$node);

                // Add new attributes
                foreach ($node->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Add child nodes
                $this->mergeXML($subNode, $node);

                continue;
            }

            foreach ($old[$name] as $oldNode) {
                if (!in_array($name, $removed, true)) {
                    $removed[] = $name;
                    unset($xml1->$name);
                }

                // Create node
                $subNode = $xml1->addChild($name);

                // Set node content (text)
                $text = trim((string)$node);
                if ($text !== '') {
                    dom_import_simplexml($subNode)->textContent = $text;
                }

                // Add old attributes
                foreach ($oldNode->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Add new attributes
                foreach ($node->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Merge old child nodes
                $this->mergeXML($subNode, $oldNode);

                // Merge new child nodes
                $this->mergeXML($subNode, $node);
            }
        }
    }

}
